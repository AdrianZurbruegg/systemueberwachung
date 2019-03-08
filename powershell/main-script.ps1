# Parameter with which the user declares what type of system the script is running on.
param(
    [Parameter()]
    [ValidateNotNullOrEmpty()]
    [ValidateSet('Windows', 'Linux')]
    [string]$hostSystem=$(throw "Operating system is mandatory, please provide the value 'Windows' or 'Linux'")
)

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

    }


    # Gets perfomance specific information about the system.
    function getPerformanceInfo{

    }

    # Call functions.
    getBasicInfo
    getPerformanceInfo
}