# functions in this file are used to modify the idf file
#	such that monthly data are included in the reports
# Mujing Wang 
# April 10th, 2013

# No longer needed as of June 12th., 2013

def add_monthly_electricity(idf_file)
	idf_file.write("\nOutput:Table:Monthly,
  End Use Energy Consumption Electricity Monthly,   ! Name
  3,                                                ! Digits After Decimal
  InteriorLights:Electricity,  !- Variable or Meter 1 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 1
  ExteriorLights:Electricity,  !- Variable or Meter 2 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 2
  InteriorEquipment:Electricity,  !- Variable or Meter 3 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 3
  ExteriorEquipment:Electricity,  !- Variable or Meter 4 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 4
  Fans:Electricity,        !- Variable or Meter 5 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 5
  Pumps:Electricity,       !- Variable or Meter 6 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 6
  Heating:Electricity,     !- Variable or Meter 7 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 7
  Cooling:Electricity,     !- Variable or Meter 8 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 8
  HeatRejection:Electricity,  !- Variable or Meter 9 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 9
  Humidifier:Electricity,  !- Variable or Meter 10 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 10
  HeatRecovery:Electricity,!- Variable or Meter 11 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 11
  WaterSystems:Electricity,!- Variable or Meter 12 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 12
  Cogeneration:Electricity,!- Variable or Meter 13 Name
  SumOrAverage;            !- Aggregation Type for Variable or Meter 13
")
end

def add_monthly_gasoline(idf_file)
	idf_file.write("\nOutput:Table:Monthly,
  End Use Energy Consumption Gasoline Monthly,   ! Name
  3,                                                ! Digits After Decimal
  Cooling:Gasoline,                              ! Variable or Meter 1 Name
  SumOrAverage,                                     ! Aggregation Type for Variable or Meter 1
  InteriorLights:Gasoline,
  SumOrAverage,
  Heating:Gasoline,
  SumOrAverage;
")
end

def add_monthly_naturalgas(idf_file)
	idf_file.write("\nOutput:Table:Monthly,
  End Use Energy Consumption Natural Gas Monthly,   ! Name
  3,                                                ! Digits After Decimal
  InteriorEquipment:Gas,   !- Variable or Meter 1 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 1
  ExteriorEquipment:Gas,   !- Variable or Meter 2 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 2
  Heating:Gas,             !- Variable or Meter 3 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 3
  Cooling:Gas,             !- Variable or Meter 4 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 4
  WaterSystems:Gas,        !- Variable or Meter 5 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 5
  Cogeneration:Gas,        !- Variable or Meter 6 Name
  SumOrAverage;            !- Aggregation Type for Variable or Meter 6
")
end

def add_monthly_diesel(idf_file)
	idf_file.write("\nOutput:Table:Monthly,
  End Use Energy Consumption Diesel Monthly,   ! Name
  3,                                                ! Digits After Decimal
  Cooling:Diesel,                              ! Variable or Meter 1 Name
  SumOrAverage,                                     ! Aggregation Type for Variable or Meter 1
  InteriorLights:Diesel,
  SumOrAverage,
  Heating:Diesel,
  SumOrAverage;
")
end


def add_GHG_emmission(idf_file) 
	idf_file.write("\n!-   ===========  ALL OBJECTS IN CLASS: OUTPUT:ENVIRONMENTALIMPACTFACTORS ===========

Output:EnvironmentalImpactFactors,
    RunPeriod;               !- Reporting Frequency


!-   ===========  ALL OBJECTS IN CLASS: ENVIRONMENTALIMPACTFACTORS ===========

EnvironmentalImpactFactors,
    0.3,                     !- District Heating Efficiency
    3,                       !- District Cooling COP {W/W}
    0.25,                    !- Steam Conversion Efficiency
    80.7272,                 !- Total Carbon Equivalent Emission Factor From N2O {kg/kg}
    6.2727,                  !- Total Carbon Equivalent Emission Factor From CH4 {kg/kg}
    0.2727;                  !- Total Carbon Equivalent Emission Factor From CO2 {kg/kg}


!-   ===========  ALL OBJECTS IN CLASS: FUELFACTORS ===========
! Virginia electricity source and emission factors based on Deru and Torcellini 2007

  FuelFactors,
    Electricity,             !- Existing Fuel Resource Name
    kg,                      !- Units of Measure
    ,                        !- Energy per Unit Factor
    3.576,                   !- Source Energy Factor {J/J}
    ,                        !- Source Energy Schedule Name
    1.681E+02,               !- CO2 Emission Factor {g/MJ}
    ,                        !- CO2 Emission Factor Schedule Name
    1.228E-01,               !- CO Emission Factor {g/MJ}
    ,                        !- CO Emission Factor Schedule Name
    3.167E-01,               !- CH4 Emission Factor {g/MJ}
    ,                        !- CH4 Emission Factor Schedule Name
    3.361E-01,               !- NOx Emission Factor {g/MJ}
    ,                        !- NOx Emission Factor Schedule Name
    3.528E-03,               !- N2O Emission Factor {g/MJ}
    ,                        !- N2O Emission Factor Schedule Name
    1.014E+00,               !- SO2 Emission Factor {g/MJ}
    ,                        !- SO2 Emission Factor Schedule Name
    0.0,                     !- PM Emission Factor {g/MJ}
    ,                        !- PM Emission Factor Schedule Name
    9.139E-03,               !- PM10 Emission Factor {g/MJ}
    ,                        !- PM10 Emission Factor Schedule Name
    0.0,                     !- PM2.5 Emission Factor {g/MJ}
    ,                        !- PM2.5 Emission Factor Schedule Name
    0.0,                     !- NH3 Emission Factor {g/MJ}
    ,                        !- NH3 Emission Factor Schedule Name
    1.106E-02,               !- NMVOC Emission Factor {g/MJ}
    ,                        !- NMVOC Emission Factor Schedule Name
    4.083E-06,               !- Hg Emission Factor {g/MJ}
    ,                        !- Hg Emission Factor Schedule Name
    1.286E-05,               !- Pb Emission Factor {g/MJ}
    ,                        !- Pb Emission Factor Schedule Name
    0.063066,                !- Water Emission Factor {L/MJ}
    ,                        !- Water Emission Factor Schedule Name
    0,                       !- Nuclear High Level Emission Factor {g/MJ}
    ,                        !- Nuclear High Level Emission Factor Schedule Name
    0;                       !- Nuclear Low Level Emission Factor {m3/MJ}

! Deru and Torcellini 2007
! Source Energy and Emission Factors for Energy Use in Buildings
! NREL/TP-550-38617
! source factor and Higher Heating Values from Table 5
! post-combustion emission factors for boiler from Table 9 (with factor of 1000 correction for natural gas)

  FuelFactors,
    NaturalGas,              !- Existing Fuel Resource Name
    m3,                      !- Units of Measure
    37631000,                !- Energy per Unit Factor
    1.092,                   !- Source Energy Factor {J/J}
    ,                        !- Source Energy Schedule Name
    5.21E+01,                !- CO2 Emission Factor {g/MJ}
    ,                        !- CO2 Emission Factor Schedule Name
    3.99E-02,                !- CO Emission Factor {g/MJ}
    ,                        !- CO Emission Factor Schedule Name
    1.06E-03,                !- CH4 Emission Factor {g/MJ}
    ,                        !- CH4 Emission Factor Schedule Name
    4.73E-02,                !- NOx Emission Factor {g/MJ}
    ,                        !- NOx Emission Factor Schedule Name
    1.06E-03,                !- N2O Emission Factor {g/MJ}
    ,                        !- N2O Emission Factor Schedule Name
    2.68E-04,                !- SO2 Emission Factor {g/MJ}
    ,                        !- SO2 Emission Factor Schedule Name
    0.0,                     !- PM Emission Factor {g/MJ}
    ,                        !- PM Emission Factor Schedule Name
    3.59E-03,                !- PM10 Emission Factor {g/MJ}
    ,                        !- PM10 Emission Factor Schedule Name
    0.0,                     !- PM2.5 Emission Factor {g/MJ}
    ,                        !- PM2.5 Emission Factor Schedule Name
    0,                       !- NH3 Emission Factor {g/MJ}
    ,                        !- NH3 Emission Factor Schedule Name
    2.61E-03,                !- NMVOC Emission Factor {g/MJ}
    ,                        !- NMVOC Emission Factor Schedule Name
    1.11E-07,                !- Hg Emission Factor {g/MJ}
    ,                        !- Hg Emission Factor Schedule Name
    2.13E-07,                !- Pb Emission Factor {g/MJ}
    ,                        !- Pb Emission Factor Schedule Name
    0,                       !- Water Emission Factor {L/MJ}
    ,                        !- Water Emission Factor Schedule Name
    0,                       !- Nuclear High Level Emission Factor {g/MJ}
    ,                        !- Nuclear High Level Emission Factor Schedule Name
    0;                       !- Nuclear Low Level Emission Factor {m3/MJ}

")
end

def add_energy_cost(idf_file)
	idf_file.write("\n
! ***ECONOMICS***


 UtilityCost:Tariff,
    Bldg101_ELECTRIC_RATE,  !- Name
    Electricity:Facility,    !- Output Meter Name
    kWh,                     !- Conversion Factor Choice
    ,                        !- Energy Conversion Factor
    ,                        !- Demand Conversion Factor
    ,                        !- Time of Use Period Schedule Name
    ,                        !- Season Schedule Name
    ,                        !- Month Schedule Name
    ,                        !- Demand Window Length
    ,                        !- Monthly Charge or Variable Name
    ,                        !- Minimum Monthly Charge or Variable Name
    ,                        !- Real Time Pricing Charge Schedule Name
    ,                        !- Customer Baseline Load Schedule Name
    Comm Elec;               !- Group Name

 UtilityCost:Tariff,
    Bldg101_GAS_RATE,        !- Name
    Gas:Facility,            !- Output Meter Name
    CCF,                     !- Conversion Factor Choice
    ,                        !- Energy Conversion Factor
    ,                        !- Demand Conversion Factor
    ,                        !- Time of Use Period Schedule Name
    ,                        !- Season Schedule Name
    ,                        !- Month Schedule Name
    ,                        !- Demand Window Length
    ,                        !- Monthly Charge or Variable Name
    ,                        !- Minimum Monthly Charge or Variable Name
    ,                        !- Real Time Pricing Charge Schedule Name
    ,                        !- Customer Baseline Load Schedule Name
    Comm Gas;                !- Group Name


!-   ===========  ALL OBJECTS IN CLASS: UTILITYCOST:CHARGE:SIMPLE ===========

 UtilityCost:Charge:Simple,
    GENERATION_CHARGE,       !- Name
    Bldg101_ELECTRIC_RATE,  !- Tariff Name
    totalEnergy,             !- Source Variable
    Annual,                  !- Season
    EnergyCharges,           !- Category Variable Name
    0.147;                   !- Cost per Unit Value or Variable Name
 
 UtilityCost:Charge:Simple,
    GAS_GCR_CHARGE,          !- Name
    Bldg101_GAS_RATE,       !- Tariff Name
    totalEnergy,             !- Source Variable
    Annual,                  !- Season
    EnergyCharges,           !- Category Variable Name
    1.35;                    !- Cost per Unit Value or Variable Name

Output:Table:Monthly,
  End Use Energy Consumption Natural Gas Monthly,   ! Name
  3,                                                ! Digits After Decimal
  InteriorEquipment:Gas,   !- Variable or Meter 1 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 1
  ExteriorEquipment:Gas,   !- Variable or Meter 2 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 2
  Heating:Gas,             !- Variable or Meter 3 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 3
  Cooling:Gas,             !- Variable or Meter 4 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 4
  WaterSystems:Gas,        !- Variable or Meter 5 Name
  SumOrAverage,            !- Aggregation Type for Variable or Meter 5
  Cogeneration:Gas,        !- Variable or Meter 6 Name
  SumOrAverage;            !- Aggregation Type for Variable or Meter 6
")
end




# add ZoneComponentLoadSummary to summaryreport in idf_file
def add_load_summary_report(idf_file)

	puts `sed  's/\\(Output:Table:SummaryReports,\\)/\\1\\n  ZoneComponentLoadSummary,/g'  #{idf_file} > #{idf_file}_new`
	`mv #{idf_file}_new #{idf_file}`
end

# modify the default unit system to I-P unit in idf_file  
def convert_unit_to_ip(idf_file)

	puts `sed  's/\\(HTML;.*\\)/HTML,\\n  InchPound;/g'  #{idf_file} > #{idf_file}_new`
	`mv #{idf_file}_new #{idf_file}`
end
	



