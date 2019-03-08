# Parameter with which the user declares what type of system the script is running on.
param(
    [Parameter()]
    [ValidateNotNullOrEmpty()]
    [ValidateSet('Windows', 'Linux', 'Debug')]
    [string]$hostSystem=$(throw "Operating system is mandatory, please provide the value 'Windows' or 'Linux'")
)



#------------------------------------------------------------------------------------------------------------------------------------------------------------------


# This bit of code establishes the Database connection.
# These 2 if loops decide which assembly to load. (Windows or Linux) This is required to enable the database connection.
if ($hostSystem -eq 'Windows'){

    # Load windows assembly (Connector/.NET driver)
    [System.Reflection.Assembly]::LoadWithPartialName("MySql.Data")
}
if ($hostSystem -eq 'Linux'){

    # Load linux assembly (.NET & MONO driver)
    add-type -Assembly /opt/microsoft/powershell/6/MySql.Data.dll
}

# Build connection string for database.
[string]$sMySQLUserName = 'admin'
[string]$sMySQLPW = 'London99!'
[string]$sMySQLDB = 'systemueberwachung'
[string]$sMySQLHost = '10.0.100.104'
[string]$sConnectionString = "server="+$sMySQLHost+";port=3306;uid=" + $sMySQLUserName + ";pwd=" + $sMySQLPW + ";database="+$sMySQLDB

# Open database connection.
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


#------------------------------------------------------------------------------------------------------------------------------------------------------------------


# Get an instance of all objects need for a SELECT query. The Command object
$oMYSQLCommand = New-Object MySql.Data.MySqlClient.MySqlCommand
# DataAdapter Object
$oMYSQLDataAdapter = New-Object MySql.Data.MySqlClient.MySqlDataAdapter
# And the DataSet Object
$oMYSQLDataSet = New-Object System.Data.DataSet
# Assign the established MySQL connection
$oMYSQLCommand.Connection=$oConnection
# Define a SELECT query
$oMYSQLCommand.CommandText='SELECT * from availabilityClass'
$oMYSQLDataAdapter.SelectCommand=$oMYSQLCommand
# Execute the query
$iNumberOfDataSets=$oMYSQLDataAdapter.Fill($oMYSQLDataSet, "data")

foreach($oDataSet in $oMYSQLDataSet.tables[0])
{
    write-host "Klasse:" $oDataSet.type
}

# Get hostname of the system.
$hostname = hostname
$hostname

if ($hostSystem -eq 'Windows'){

    # Set hostSystem name. This is to make sure every database entry of "hostSystem" is written the same way since powershell parameters are not case sensitive.
    $hostSystem = 'Windows'
    $hostSystem

    # Gets some basic information of the system. (Function has more than 20 lines, since a certain amount of information is required)
    function getBasicInfo{

        # Note: all network specific informations are only about IPv4 .

        # Get IP-Address.
        $networkInfo = Get-NetIPConfiguration
        $ipAddress = $networkInfo.IPv4Address.IPAddress
        $ipAddress

        # Get subnetmask.
        $subnetmask = Get-WmiObject Win32_NetworkAdapterConfiguration
        $subnetmask = $subnetmask.IPSubnet[0]
        $subnetmask

        # Get gateway.
        $gateway = $networkInfo.IPv4DefaultGateway.NextHop
        $gateway

        # Get DNS-Servers.
        $dnsServer = $networkInfo.DNSServer.ServerAddresses
        $dnsServer

        # Get disks (Volume & Partition)
        $volumes = Get-Partition | Get-Volume | Select-Object -Property DriveLetter, FileSystemLabel, FileSystem, HealthStatus, OperationalStatus, @{n="Size";e={[math]::Round($_.Size/1GB,2)}}, @{n="Used";e={[math]::Round([math]::Round($_.Size/1GB,2) - [math]::Round($_.SizeRemaining/1GB,2),2)}} | ConvertTo-Csv -NoTypeInformation | Select-Object -Skip 1 | % {$_ -replace '"', ''} | % {$_ -replace ' ', ''} | Sort-Object -Descending
        $volumes

        # Get CPU processors.
        $computerInfo = Get-ComputerInfo
        $processor = $computerInfo.CsProcessors.Name[0]
        $processor

        $cores = $computerInfo.CsNumberOfProcessors
        $cores

        $logicalCores = $computerInfo.CsNumberOfLogicalProcessors
        $logicalCores

        # Get RAM amount.
        $ram = $computerInfo.CsTotalPhysicalMemory
        $ram = [Math]::Round($ram / 1GB)
        $ram

        # Get operating system  & manufacturer info.
        $operatingSystem = $computerInfo.OsName
        $operatingSystem

        $manufacturerInfo = $computerInfo.CsManufacturer + " " + $computerInfo.CsModel
        $manufacturerInfo

        # Get system uptime.
        [string]$days = $computerInfo.OsUptime.Days
        [string]$hours = $computerInfo.OsUptime.Hours
        [string]$minutes = $computerInfo.OsUptime.Minutes
        [string]$seconds = $computerInfo.OsUptime.Seconds
        $uptime = "Days:" + $days + " Hours:" + $hours + " Minutes:" + $minutes + " Seconds:" + $seconds
        $uptime

    }


    # Gets perfomance specific information about the system.
    function getPerformanceInfo{

        # Get disk usage in percent.
        $diskUsage = Get-Partition | Get-Volume | Select-Object -Property DriveLetter, @{n="Percentage";e={[math]::Round(100-($_.SizeRemaining*100/$_.Size))}} | ConvertTo-Csv -NoTypeInformation | Select-Object -Skip 1 | % {$_ -replace '"',''} | % {$_ -replace ' ',''} | Sort-Object -Descending
        $diskUsage

        # Get CPU usage in percent.
        $cpuUsage = Get-WmiObject win32_processor | Measure-Object -property LoadPercentage -Average | Select Average
        $cpuUsage = $cpuUsage.Average
        $cpuUsage

        # Get RAM usage in percent.
        $os = Get-Ciminstance Win32_OperatingSystem
        $ramUsage = [math]::Round(100-($os.FreePhysicalMemory/$os.TotalVisibleMemorySize)*100,2)
        $ramUsage

    }

    # Call functions.
    getBasicInfo
    getPerformanceInfo
}


if ($hostSystem -eq 'Linux'){

    # Note: Since powershell has only been available for linux for a short time, the functionality is limited. Therefore, almost all of the following lines are linux bash commands and not powershell specific commands.

    # Set hostSystem name. This is to make sure every database entry of "hostSystem" is written the same way since powershell parameters are not case sensitive.
    $hostSystem = 'Linux'

    # Gets some basic information of the system. (Function has more than 20 lines, since a certain amount of information is required)
    function getBasicInfo{

        # Note: all network specific informations are only about IPv4.

        # Get IP-Address.
        $ipAddress = hostname -I | awk '{$1=$1};1'
        $ipAddress

        # Get subnetmask.
        $subnetmask = ip -o -f inet addr show | awk '/scope global/ {print $4}'
        $subnetmask

        # Get gateway.
        $gateway = route -n | grep 'UG[ \t]' | awk '{print $2}'
        $gateway

        # Get DNS-Servers.
        $dnsServer = cat /run/systemd/resolve/resolv.conf | tail -2 | tr -d nameserver | awk '{$1=$1};1'
        $dnsServer

        # Get disks (Volume & Partition)
        $volumes = df -hlT | grep -v 'tmpfs\|loop' | sed -e 's/  */,/g' -e 's/$/ /g' | awk 'NR!=1' | awk '{$1=$1};1' | cut -d',' '-f5,6' --complement | tr -d 'G'
        $volumes

        # Get CPU processors.
        $processor = less /proc/cpuinfo | grep 'model name' | head -n 1 | cut -d':' -f2 | awk '{$1=$1};1'
        $processor

        $cores = lscpu | grep 'Socket' | cut -d':' -f2 | awk '{$1=$1};1'
        $cores

        $logicalCores = nproc
        $logicalCores

        # Get RAM amount.
        $ram = awk '/MemTotal/ {print $2}' /proc/meminfo
        $ram = [Math]::Round(($ram / 1024)/1024)
        $ram

        # Get operating system & manufacturer info.
        $operatingSystem =  cat /etc/os-release | grep 'PRETTY_NAME' | cut -d'=' -f2 | sed 's/\"//g'
        $operatingSystem

        $vendor = cat /sys/devices/virtual/dmi/id/sys_vendor
        $productName = cat /sys/devices/virtual/dmi/id/product_name
        $manufacturerInfo = $vendor + " " + $productName
        $manufacturerInfo

        # Get system uptime.
        $uptime = Get-UpTime
        $days = $uptime.Days
        $hours = $uptime.Hours
        $minutes = $uptime.Minutes
        $seconds = $uptime.Seconds
        $uptime = "Days:" + $days + " Hours:" + $hours + " Minutes:" + $minutes + " Seconds:" + $seconds
        $uptime

    }


    # Gets perfomance specific information about the system.
    function getPerformanceInfo{

        # Get disk usage in percent.
        $diskUsage = df -hlT | grep -v 'tmpfs\|loop' | awk 'NR!=1' | sed -e 's/  */,/g' -e 's/$/ /g' | awk '{$1=$1};1' | cut -d',' '-f1,6' | tr -d '%'
        $diskUsage

        # Get CPU usage in percent.
        $cpuUsage = (100-(vmstat 1 2 | tail -1 | awk '{print $15}'))
        $cpuUsage

        # Get RAM usage in percent.
        $ramUsage = free | grep Mem | awk '{print $3/$2 * 100.0}'
        $ramUsage = [Math]::Round($ramUsage)
        $ramUsage

    }

    # Call functions.
    getBasicInfo
    getPerformanceInfo
}