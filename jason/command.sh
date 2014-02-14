# NODE COMMAND
xvfb-run -a node openstudio-run.js;

# xvfb-run = to run script without GUI, needed by X server frame Buffer for OpenStudio
# -a = auto-allocates display server for program so multiple users can run at same time
# to INSTALL xvfb on Ubuntu:  sudo apt-get install xvfb

# RUBY COMMAND EQUIVALENT
# xvfb-run -a ruby x-retrofit-manager-MINIMUM.rb 1234 eebhub@email.com "JASON" "USA_IL_Chicago-OHare.Intl.AP.725300_TMY3" SmallOffice WholeBuilding 3 15000 “Metal” "Concrete Mass" 0.4 Rectangle 100 50
# Rather than pass variables as arguments in Node Command, read them from buildingData.json ##


