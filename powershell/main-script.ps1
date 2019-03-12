# This script collects various information from different machines.
# It works for both windows and linux systems.
# The collected information will be stored in a database.
# Autor: Adrian Zurbrügg


#------------------------------------------------------------------------------------------------------------------------------------------------------------------


# Parameter with which the user declares what type of system the script is running on.
param(
    [Parameter()]
    [ValidateNotNullOrEmpty()]
    [ValidateSet('windows', 'linux')]
    [string]$hostSystem=$(throw "Operating system is mandatory, please provide the value 'windows' or 'linux'"),
    [Parameter()]
    [ValidateNotNullOrEmpty()]
    [ValidateSet('standard', 'debug')]
    [string]$debugStatus=$(throw "Debug status is mandatory, please provide the value 'standard' or 'debug'")
)


#------------------------------------------------------------------------------------------------------------------------------------------------------------------


# This bit of code establishes the Database connection.

# These 2 if loops decide which assembly to load. (Windows or Linux) This is required to enable the database connection.
if ($hostSystem -eq 'windows'){

    # Load windows assembly (Connector/.NET driver)
    [System.Reflection.Assembly]::LoadWithPartialName("MySql.Data")
}
if ($hostSystem -eq 'linux'){

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


# Declare functions which both (Windows & Linux) uses to insert data to the database.

# Inserts or updates host data in the database. It performs an update if the hostname already exists, else an insert.
function insertDataHostInfo($hostname, $hostSystem){

    # insertDataHostInfo is a procedure in the database, so all we do here is call the procedure with some parameters.
    $insertQuery = "call i_or_u_hostInfo('$hostname', '$hostSystem');"
    $oMYSQLCommand = New-Object MySql.Data.MySqlClient.MySqlCommand $insertQuery, $oConnection
    $iRowsAffected=$oMYSQLCommand.ExecuteNonQuery()
}

# Inserts or updates basic data in the database. It performs an update if the hostname already exists, else an insert.
function insertDataBasicInfo($hostname, $ipAddress, $subnetmask, $gateway, $dnsServer, $volumes, $processor, $cores, $logicalCores, $ram, $operatingSystem, $manufacturerInfo, $uptime){

    # insertDataBasicInfo is a procedure in the database, so all we do here is call the procedure with some parameters.
    $insertQuery = "call i_or_u_basicInfo('$hostname', '$ipAddress','$subnetmask', '$gateway', '$dnsServer', '$volumes', '$processor', '$cores', '$logicalCores', '$ram', '$operatingSystem', '$manufacturerInfo', '$uptime');"
    $oMYSQLCommand = New-Object MySql.Data.MySqlClient.MySqlCommand $insertQuery, $oConnection
    $iRowsAffected=$oMYSQLCommand.ExecuteNonQuery()
}

# Inserts performance data in the database.
function insertDataPerfomanceInfo($hostname, $diskUsage, $cpuUsage, $ramUsage){

    # insertDataPerfomanceInfo is a procedure in the database, so all we do here is call the procedure with some parameters.
    $insertQuery = "call insertPerformanceInfo('$hostname', '$diskUsage','$cpuUsage', '$ramUsage');"
    $oMYSQLCommand = New-Object MySql.Data.MySqlClient.MySqlCommand $insertQuery, $oConnection
    $iRowsAffected=$oMYSQLCommand.ExecuteNonQuery()
}

# Insert placeholder value in availabilityInfo. This is necessary because otherwise a newly added server
# will not be displayed on the website until a corresponding entry has been generated in the availabilityInfo table.
# This placeholder bridges the time until the availability script runs and generates an entry.
function insertDataAvailabilityInfo($ping, $ipAddress){

    # insertAvailabilityInfo is a procedure in the database, so all we do here is call the procedure with some parameters.
    $insertQuery = "call i_or_u_availabilityInfoPlaceholder('$ping', '$ipAddress');"
    $oMYSQLCommand = New-Object MySql.Data.MySqlClient.MySqlCommand $insertQuery, $oConnection
    $iRowsAffected=$oMYSQLCommand.ExecuteNonQuery()
}


#------------------------------------------------------------------------------------------------------------------------------------------------------------------


# Depending on which parameter was specified, the script runs for windows or linux.
# The script collects the same data for both operating systems and calls the corresponding functions to insert it to the database.

# Get hostname of the system.
$hostname = hostname

if ($hostSystem -eq 'windows' -and $debugStatus -eq 'standard'){

    # Set hostSystem name. This is to make sure every database entry of "hostSystem" is written the same way since powershell parameters are not case sensitive.
    $hostSystem = 'Windows'

    # Gets some basic information of the system. (Function has more than 20 lines, since a certain amount of information is required)
    function getBasicInfo{

        $networkInfo = Get-NetIPConfiguration
        $subnetmask = Get-WmiObject Win32_NetworkAdapterConfiguration
        $computerInfo = Get-ComputerInfo

        # Note: all network specific informations are only about IPv4 .

        # Get IP-Address.
        $ipAddress = $networkInfo.IPv4Address.IPAddress

        # Get subnetmask.
        $subnetmask = $subnetmask.IPSubnet[0]

        # Get gateway.
        $gateway = $networkInfo.IPv4DefaultGateway.NextHop

        # Get DNS-Servers.
        $dnsServer = $networkInfo.DNSServer.ServerAddresses

        # Get disks (Volume & Partition)
        $volumes = Get-Partition | Get-Volume | Select-Object -Property DriveLetter, FileSystemLabel, FileSystem, HealthStatus, OperationalStatus, @{n="Size";e={[math]::Round($_.Size/1GB,2)}}, @{n="Used";e={[math]::Round([math]::Round($_.Size/1GB,2) - [math]::Round($_.SizeRemaining/1GB,2),2)}} | ConvertTo-Csv -NoTypeInformation | Select-Object -Skip 1 | % {$_ -replace '"', ''} | % {$_ -replace ' ', ''} | Sort-Object -Descending

        # Get CPU processors.
        $processor = $computerInfo.CsProcessors.Name[0]
        $cores = $computerInfo.CsNumberOfProcessors
        $logicalCores = $computerInfo.CsNumberOfLogicalProcessors

        # Get RAM amount.
        $ram = $computerInfo.CsTotalPhysicalMemory
        $ram = [Math]::Round($ram / 1GB)

        # Get operating system  & manufacturer info.
        $operatingSystem = $computerInfo.OsName
        $manufacturerInfo = $computerInfo.CsManufacturer + " " + $computerInfo.CsModel

        # Get system uptime.
        [string]$days = $computerInfo.OsUptime.Days
        [string]$hours = $computerInfo.OsUptime.Hours
        [string]$minutes = $computerInfo.OsUptime.Minutes
        [string]$seconds = $computerInfo.OsUptime.Seconds
        $uptime = "Days:" + $days + " Hours:" + $hours + " Minutes:" + $minutes + " Seconds:" + $seconds

        insertDataBasicInfo($hostname) ($ipAddress) ($subnetmask) ($gateway) ($dnsServer) ($volumes) ($processor) ($cores) ($logicalCores) ($ram) ($operatingSystem) ($manufacturerInfo) ($uptime)
        insertDataAvailabilityInfo(2) ($ipAddress)
    }


    # Gets perfomance specific information about the system.
    function getPerformanceInfo{

        # Get disk usage in percent.
        $diskUsage = Get-Partition | Get-Volume | Select-Object -Property DriveLetter, @{n="Percentage";e={[math]::Round(100-($_.SizeRemaining*100/$_.Size))}} | ConvertTo-Csv -NoTypeInformation | Select-Object -Skip 1 | % {$_ -replace '"',''} | % {$_ -replace ' ',''} | Sort-Object -Descending

        # Get CPU usage in percent.
        $cpuUsage = Get-WmiObject win32_processor | Measure-Object -property LoadPercentage -Average | Select Average
        $cpuUsage = $cpuUsage.Average

        # Get RAM usage in percent.
        $os = Get-Ciminstance Win32_OperatingSystem
        $ramUsage = [math]::Round(100-($os.FreePhysicalMemory/$os.TotalVisibleMemorySize)*100,2)

        insertDataPerfomanceInfo($hostname) ($diskUsage) ($cpuUsage) ($ramUsage)
    }

    # Call functions.
    insertDataHostInfo($hostname) ($hostSystem)
    getBasicInfo
    getPerformanceInfo
}


if ($hostSystem -eq 'linux'  -and $debugStatus -eq 'standard'){

    # Note: Since powershell has only been available for linux for a short time, the functionality is limited. Therefore, almost all of the following lines are linux bash commands and not powershell specific commands.

    # Set hostSystem name. This is to make sure every database entry of "hostSystem" is written the same way since powershell parameters are not case sensitive.
    $hostSystem = 'Linux'

    # Gets some basic information of the system. (Function has more than 20 lines, since a certain amount of information is required)
    function getBasicInfo{

        # Note: all network specific informations are only about IPv4.

        # Get IP-Address.
        $ipAddress = hostname -I |tr -d ' '

        # Get subnetmask.
        $subnetmask = ip -o -f inet addr show | awk '/scope global/ {print $4}'

        # Get gateway.
        $gateway = route -n | grep 'UG[ \t]' | awk '{print $2}'

        # Get DNS-Servers.
        $dnsServer = cat /run/systemd/resolve/resolv.conf | tail -2 | tr -d nameserver | awk '{$1=$1};1'

        # Get disks (Volume & Partition)
        $volumes = df -hlT | grep -v 'tmpfs\|loop' | sed -e 's/  */,/g' -e 's/$/ /g' | awk 'NR!=1' | awk '{$1=$1};1' | cut -d',' '-f5,6' --complement | tr -d 'G'

        # Get CPU processors.
        $processor = less /proc/cpuinfo | grep 'model name' | head -n 1 | cut -d':' -f2 | awk '{$1=$1};1'
        $cores = lscpu | grep 'Socket' | cut -d':' -f2 | awk '{$1=$1};1'
        $logicalCores = nproc

        # Get RAM amount.
        $ram = awk '/MemTotal/ {print $2}' /proc/meminfo
        $ram = [Math]::Round(($ram / 1024)/1024)

        # Get operating system & manufacturer info.
        $operatingSystem =  cat /etc/os-release | grep 'PRETTY_NAME' | cut -d'=' -f2 | sed 's/\"//g' | head -n 1
        $vendor = cat /sys/devices/virtual/dmi/id/sys_vendor
        $productName = cat /sys/devices/virtual/dmi/id/product_name
        $manufacturerInfo = $vendor + " " + $productName

        # Get system uptime.
        $uptime = Get-UpTime
        $days = $uptime.Days
        $hours = $uptime.Hours
        $minutes = $uptime.Minutes
        $seconds = $uptime.Seconds
        $uptime = "Days:" + $days + " Hours:" + $hours + " Minutes:" + $minutes + " Seconds:" + $seconds

        insertDataBasicInfo($hostname) ($ipAddress) ($subnetmask) ($gateway) ($dnsServer) ($volumes) ($processor) ($cores) ($logicalCores) ($ram) ($operatingSystem) ($manufacturerInfo) ($uptime)
        insertDataAvailabilityInfo(2) ($ipAddress)
    }


    # Gets perfomance specific information about the system.
    function getPerformanceInfo{

        # Get disk usage in percent.
        $diskUsage = df -hlT | grep -v 'tmpfs\|loop' | sed -e 's/  */,/g' -e 's/$/ /g' | awk 'NR!=1' | awk '{$1=$1};1' | cut -d',' '-f1,6' | tr -d '%'

        # Get CPU usage in percent.
        $cpuUsage = (100-(vmstat 1 2|tail -1|awk '{print $15}'))

        # Get RAM usage in percent.
        $ramUsage = free | grep Mem | awk '{print $3/$2 * 100.0}'
        $ramUsage = [Math]::Round($ramUsage)

        insertDataPerfomanceInfo($hostname) ($diskUsage) ($cpuUsage) ($ramUsage)
    }

    # Call functions.
    insertDataHostInfo($hostname) ($hostSystem)
    getBasicInfo
    getPerformanceInfo
}




# START: Debug functions ------------------------------------------------------------------------------------------------------------------------------------------
# If the second parameter is set to 'debug' the following functions show what value the scripts get for the corresponding variable.

if ($hostSystem -eq 'windows' -and $debugStatus -eq 'debug'){

    function getBasicInfoDebug{

        $networkInfo = Get-NetIPConfiguration
        $subnetmask = Get-WmiObject Win32_NetworkAdapterConfiguration
        $computerInfo = Get-ComputerInfo

        # Get IP-Address.
        $ipAddress = $networkInfo.IPv4Address.IPAddress
        Write-Host('IP-Adresse: ', $ipAddress)

        # Get subnetmask.
        $subnetmask = $subnetmask.IPSubnet[0]
        Write-Host('Subnetzmaske: ', $subnetmask)

        # Get gateway.
        $gateway = $networkInfo.IPv4DefaultGateway.NextHop
        Write-Host('Gateway: ', $gateway)

        # Get DNS-Servers.
        $dnsServer = $networkInfo.DNSServer.ServerAddresses
        Write-Host('DNS-Server: ', $dnsServer)

        # Get disks (Volume & Partition)
        $volumes = Get-Partition | Get-Volume | Select-Object -Property DriveLetter, FileSystemLabel, FileSystem, HealthStatus, OperationalStatus, @{n="Size";e={[math]::Round($_.Size/1GB,2)}}, @{n="Used";e={[math]::Round([math]::Round($_.Size/1GB,2) - [math]::Round($_.SizeRemaining/1GB,2),2)}} | ConvertTo-Csv -NoTypeInformation | Select-Object -Skip 1 | % {$_ -replace '"', ''} | % {$_ -replace ' ', ''} | Sort-Object -Descending
        Write-Host('Volumes: ', $volumes)

        # Get CPU processors.
        $processor = $computerInfo.CsProcessors.Name[0]
        Write-Host('Prozessor: ', $processor)

        $cores = $computerInfo.CsNumberOfProcessors
        Write-Host('Anzahl Kerne: ', $cores)

        $logicalCores = $computerInfo.CsNumberOfLogicalProcessors
        Write-Host('Anzahl logischer Kerne: ', $logicalCores)

        # Get RAM amount.
        $ram = $computerInfo.CsTotalPhysicalMemory
        $ram = [Math]::Round($ram / 1GB)
        Write-Host('Anzahl RAM: ', $ram)

        # Get operating system  & manufacturer info.
        $operatingSystem = $computerInfo.OsName
        Write-Host('Betriebssystem: ', $operatingSystem)

        $manufacturerInfo = $computerInfo.CsManufacturer + " " + $computerInfo.CsModel
        Write-Host('Hersteller Information: ', $manufacturerInfo)

        # Get system uptime.
        [string]$days = $computerInfo.OsUptime.Days
        [string]$hours = $computerInfo.OsUptime.Hours
        [string]$minutes = $computerInfo.OsUptime.Minutes
        [string]$seconds = $computerInfo.OsUptime.Seconds
        $uptime = "Days:" + $days + " Hours:" + $hours + " Minutes:" + $minutes + " Seconds:" + $seconds
        Write-Host('Aktuelle Uptime: ', $uptime)

    }

    function getPerformanceInfoDebug{

        # Get disk usage in percent.
        $diskUsage = Get-Partition | Get-Volume | Select-Object -Property DriveLetter, @{n="Percentage";e={[math]::Round(100-($_.SizeRemaining*100/$_.Size))}} | ConvertTo-Csv -NoTypeInformation | Select-Object -Skip 1 | % {$_ -replace '"',''} | % {$_ -replace ' ',''} | Sort-Object -Descending
        Write-Host('Disk-Nutzung: ', $diskUsage)

        # Get CPU usage in percent.
        $cpuUsage = Get-WmiObject win32_processor | Measure-Object -property LoadPercentage -Average | Select Average
        $cpuUsage = $cpuUsage.Average
        Write-Host('CPU-Nutzung: ', $cpuUsage)

        # Get RAM usage in percent.
        $os = Get-Ciminstance Win32_OperatingSystem
        $ramUsage = [math]::Round(100-($os.FreePhysicalMemory/$os.TotalVisibleMemorySize)*100,2)
        Write-Host('RAM-Nutzung: ', $ramUsage)

    }

    # Call functions.
    getBasicInfoDebug
    getPerformanceInfoDebug
}

if ($hostSystem -eq 'linux'  -and $debugStatus -eq 'debug'){

    # Gets some basic information of the system. (Function has more than 20 lines, since a certain amount of information is required)
    function getBasicInfo{

        # Note: all network specific informations are only about IPv4.

        # Get IP-Address.
        $ipAddress = hostname -I |tr -d ' '
        Write-Host('IP-Adresse: ', $ipAddress)

        # Get subnetmask.
        $subnetmask = ip -o -f inet addr show | awk '/scope global/ {print $4}'
        Write-Host('Subnetzmaske: ', $subnetmask)

        # Get gateway.
        $gateway = route -n | grep 'UG[ \t]' | awk '{print $2}'
        Write-Host('Gateway: ', $gateway)

        # Get DNS-Servers.
        $dnsServer = cat /run/systemd/resolve/resolv.conf | tail -2 | tr -d nameserver | awk '{$1=$1};1'
        Write-Host('DNS-Server: ', $dnsServer)

        # Get disks (Volume & Partition)
        $volumes = df -hlT | grep -v 'tmpfs\|loop' | sed -e 's/  */,/g' -e 's/$/ /g' | awk 'NR!=1' | awk '{$1=$1};1' | cut -d',' '-f5,6' --complement | tr -d 'G'
        Write-Host('Volumes: ', $volumes)

        # Get CPU processors.
        $processor = less /proc/cpuinfo | grep 'model name' | head -n 1 | cut -d':' -f2 | awk '{$1=$1};1'
        Write-Host('Prozessor: ', $processor)

        $cores = lscpu | grep 'Socket' | cut -d':' -f2 | awk '{$1=$1};1'
        Write-Host('Anzahl Kerne: ', $cores)

        $logicalCores = nproc
        Write-Host('Anzahl Logische Kerne: ', $logicalCores)

        # Get RAM amount.
        $ram = awk '/MemTotal/ {print $2}' /proc/meminfo
        $ram = [Math]::Round(($ram / 1024)/1024)
        Write-Host('Anzahl RAM: ', $ram)

        # Get operating system & manufacturer info.
        $operatingSystem =  cat /etc/os-release | grep 'PRETTY_NAME' | cut -d'=' -f2 | sed 's/\"//g' | head -n 1
        Write-Host('Betriebssystem: ', $operatingSystem)

        $vendor = cat /sys/devices/virtual/dmi/id/sys_vendor
        $productName = cat /sys/devices/virtual/dmi/id/product_name
        $manufacturerInfo = $vendor + " " + $productName
        Write-Host('Hersteller Information: ', $manufacturerInfo)

        # Get system uptime.
        $uptime = Get-UpTime
        $days = $uptime.Days
        $hours = $uptime.Hours
        $minutes = $uptime.Minutes
        $seconds = $uptime.Seconds
        $uptime = "Days:" + $days + " Hours:" + $hours + " Minutes:" + $minutes + " Seconds:" + $seconds
        Write-Host('Aktuelle Uptime: ', $uptime)

    }


    # Gets perfomance specific information about the system.
    function getPerformanceInfo{

        # Get disk usage in percent.
        $diskUsage = df -hlT | grep -v 'tmpfs\|loop' | sed -e 's/  */,/g' -e 's/$/ /g' | awk 'NR!=1' | awk '{$1=$1};1' | cut -d',' '-f1,6' | tr -d '%'

        # Get CPU usage in percent.
        $cpuUsage = (100-(vmstat 1 2|tail -1|awk '{print $15}'))

        # Get RAM usage in percent.
        $ramUsage = free | grep Mem | awk '{print $3/$2 * 100.0}'
        $ramUsage = [Math]::Round($ramUsage)

    }

    # Call functions.
    getBasicInfo
    getPerformanceInfo
}