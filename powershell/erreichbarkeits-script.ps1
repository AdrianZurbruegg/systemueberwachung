# This bit of code establishes the Database connection

# Load linux assembly (.NET & MONO driver)
add-type -Assembly /opt/microsoft/powershell/6/MySql.Data.dll

# Build connection string for database
[string]$sMySQLUserName = 'admin'
[string]$sMySQLPW = 'London99!'
[string]$sMySQLDB = 'systemueberwachung'
[string]$sMySQLHost = '10.0.100.104'
[string]$sConnectionString = "server="+$sMySQLHost+";port=3306;uid=" + $sMySQLUserName + ";pwd=" + $sMySQLPW + ";database="+$sMySQLDB

# Open database connection
$oConnection = New-Object MySql.Data.MySqlClient.MySqlConnection($sConnectionString)
$Error.Clear()
try
{
    $oConnection.Open()
}
catch
{
    Write-Warning("Could not open a connection to Database $sMySQLDB on Host $sMySQLHost. Error: "+$Error[0].ToString())
}


# Read all IP-Address entries from the database 

# Get an instance of all objects need for a SELECT query. The Command object
$oMYSQLCommand = New-Object MySql.Data.MySqlClient.MySqlCommand
# DataAdapter Object
 $oMYSQLDataAdapter = New-Object MySql.Data.MySqlClient.MySqlDataAdapter
# And the DataSet Object 
$oMYSQLDataSet = New-Object System.Data.DataSet	
# Assign the established MySQL connection
$oMYSQLCommand.Connection=$oConnection
# Define a SELECT query
$oMYSQLCommand.CommandText='call selectIpAddress();'
$oMYSQLDataAdapter.SelectCommand=$oMYSQLCommand
# Execute the query
$iNumberOfDataSets=$oMYSQLDataAdapter.Fill($oMYSQLDataSet, "data")


# Function to convert 'false' or 'true' to either 0 or 1. This is necessary otherwise the database will return "incorrect integer value"
function convertToBoolean($value){
    if ($value -eq $false){
        $value = 0
    }
    if ($value -eq $true){
        $value = 1
    }
    return $value
}

# Inserts data in the database.
function insertDataAvailabilityInfo($ping, $ipAddress){
    # insert_or_update is a procedure in the database, so all we do here is call the procedure with some parameters
    $insertQuery = "call insertAvailabilityInfo('$ping', '$ipAddress');"
    $oMYSQLCommand = New-Object MySql.Data.MySqlClient.MySqlCommand $insertQuery, $oConnection
    $iRowsAffected=$oMYSQLCommand.ExecuteNonQuery()
}

# Iterate over every IP-Address in $oDataSet
foreach($oDataSet in $oMYSQLDataSet.tables[0]){

    # Assign the IP-Address to a variable
    $ipAddress = $oDataSet.ipAddress

    # Test connection via ICMP request packets (ping)
    $ping = Test-Connection $oDataSet.ipAddress -Quiet
    $ping = convertToBoolean($ping)
    
    insertDataAvailabilityInfo($ping) ($ipAddress)
}

