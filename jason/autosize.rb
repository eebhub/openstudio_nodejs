require 'fileutils'
require 'tempfile'

def autoSizeSubstitution(params)
	idfFilePathAndName = params['idfFilePathAndName']
	eioFilePathAndName = params['eioFilePathAndName']

	idfFile = File.new(idfFilePathAndName, "r+")
	idfTemp = Tempfile.new("tempAutoSize.idf")
	eioFile = File.new(eioFilePathAndName, "r")

	numLines = 0
	numAutosizes = 0
	componentName = ""
	readyForHeader = true
	headerName = ""

	sizingInfo = Hash.new
	infoNums = 0

	eioFile.each do |line|
		if line.include?"Sizing Information" 
			a=line.split(", ")
		
			# replace [] (brackets) with {} (braces) 
			a[3] = a[3].gsub(/\[/, '{')
			a[3] = a[3].gsub(/\]/, '}')
			#puts a[3]

			key = "#{a[1]}#{a[2]}#{a[3]}".upcase
			# remove units
			key = key.gsub(/ +\{[a-zA-Z]*\}/,"")
			# remove contents in ()
			key = key.gsub(/ +\([a-zA-Z]*\)/,"")
			key = key.gsub("MAXIMUM REHEAT WATER FLOW RATE", "MAXIMUM WATER FLOW RATE")

			sizingInfo[key] = a[4]	
		end

		if line.include?"Water Heating Coil Capacity Information"
			b=line.split(",")
			key = "#{b[1]}#{b[2]}RATED CAPACITY".upcase
			sizingInfo[key] = b[3]	
		end

	end

	#puts sizingInfo

	idfFile.each do |line|
	
		if readyForHeader
			headerName = line
			readyForHeader = false	
		end

		if line.eql?"\n"
			readyForHeader = true		
		end

		if line.include?"!- Name"
			a=line.split(/, *!- */)
			componentName = a[0].split("  ")[1]	
		end

		if line.include?"Autosize" or line.include?"AutoSize"
			numAutosizes += 1
			description = line.split(/, *!- */)[1] 

			key = (headerName+componentName+description).upcase
			key = key.gsub("\n", "")
			key = key.gsub(",", "")
			key = key.gsub(/ +\{[a-zA-Z]*\}/,"")
			#key = key.gsub(/RATED HIGH[ A-Z]*CAPACITY/, "RATED CAPACITY")
			key = key.gsub("MAXIMUM HOT WATER OR STEAM FLOW RATE", "MAXIMUM WATER FLOW RATE")
			key = key.gsub("SIZING:SYSTEMAIR LOOP HVAC 1DESIGN OUTDOOR AIR FLOW RATE", "AIRLOOPHVACAIR LOOP HVAC 1DESIGN SUPPLY AIR FLOW RATE")

			val = sizingInfo[key]
			val = val.chop
		
			if line.include?"Autosize"
				line = line.gsub("Autosize", val)
			else
				line = line.gsub("AutoSize", val)
			end
		end

		idfTemp.puts line

		numLines += 1
	end

	idfTemp.close
	FileUtils.mv(idfTemp.path, idfFilePathAndName)
end

idfFilePathAndName = "/home/platform/openstudio/outputs/idf/in.idf"
eioFilePathAndName = "/home/platform/openstudio/outputs/ENERGYPLUS/idf/in.idf/EnergyPlusPreProcess/EnergyPlus-0/eplusout.eio"

autoSizeSubstitution("idfFilePathAndName"=>idfFilePathAndName, "eioFilePathAndName"=>eioFilePathAndName)