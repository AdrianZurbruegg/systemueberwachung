# Get hostname of the system.
$hostname = hostname
$hostname

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