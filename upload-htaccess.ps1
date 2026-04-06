# FTP Upload .htaccess File
$FtpServer = "185.146.167.204"
$FtpUser = "bulkleylaundry.com"
$FtpPass = "r3x9PW?YhR#K"
$LocalFile = "c:\xampp\htdocs\bulkleylaundry\.htaccess"
$RemotePath = "/home/sites/42b/e/ec373e548f/public_html/.htaccess"
$FtpUri = "ftp://$FtpServer$RemotePath"

# Create FTP credential
$Credential = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)

Write-Host "Uploading .htaccess to $FtpUri..."

try {
    $FtpRequest = [System.Net.FtpWebRequest]::Create($FtpUri)
    $FtpRequest.Credentials = $Credential
    $FtpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $FtpRequest.UseBinary = $false
    $FtpRequest.KeepAlive = $true
    
    $FileStream = [System.IO.File]::OpenRead($LocalFile)
    $FtpStream = $FtpRequest.GetRequestStream()
    $FileStream.CopyTo($FtpStream)
    $FtpStream.Close()
    $FileStream.Close()
    
    $Response = $FtpRequest.GetResponse()
    Write-Host "Upload successful! Status: $($Response.StatusCode)" -ForegroundColor Green
    $Response.Close()
}
catch {
    Write-Host "Error uploading .htaccess: $_" -ForegroundColor Red
}
