# FTP Upload Script
$FtpServer = "185.146.167.204"
$FtpUser = "bulkleylaundry.com"
$FtpPass = "r3x9PW?YhR#K"
$LocalPath = "c:\xampp\htdocs\bulkleylaundry"
$FtpProtocol = "ftp"

# Create FTP credential
$FtpUri = "$FtpProtocol`://$FtpServer/"
$Credential = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)

# Function to upload files recursively
function Upload-FilesToFtp {
    param(
        [string]$LocalDirectory,
        [string]$RemoteDirectory
    )
    
    # Get all files in current directory
    $Files = Get-ChildItem -Path $LocalDirectory -File
    
    foreach ($File in $Files) {
        $LocalFilePath = $File.FullName
        $RemoteFilePath = "$FtpUri$RemoteDirectory/$($File.Name)"
        
        Write-Host "Uploading: $($File.Name) to $RemoteDirectory..."
        
        try {
            $FtpRequest = [System.Net.FtpWebRequest]::Create($RemoteFilePath)
            $FtpRequest.Credentials = $Credential
            $FtpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $FtpRequest.UseBinary = $true
            $FtpRequest.KeepAlive = $true
            
            $FileStream = [System.IO.File]::OpenRead($LocalFilePath)
            $FtpStream = $FtpRequest.GetRequestStream()
            $FileStream.CopyTo($FtpStream)
            $FtpStream.Close()
            $FileStream.Close()
            
            $Response = $FtpRequest.GetResponse()
            Write-Host "Uploaded: $($File.Name) - Status: $($Response.StatusCode)"
            $Response.Close()
        }
        catch {
            Write-Host "Error uploading $($File.Name): $_" -ForegroundColor Red
        }
    }
    
    # Get all subdirectories
    $Directories = Get-ChildItem -Path $LocalDirectory -Directory
    
    foreach ($Dir in $Directories) {
        $RemoteSubDir = "$RemoteDirectory/$($Dir.Name)"
        Write-Host "Creating directory: $RemoteSubDir..."
        
        try {
            $MkdirUri = "$FtpUri$RemoteSubDir/"
            $MkdirRequest = [System.Net.FtpWebRequest]::Create($MkdirUri)
            $MkdirRequest.Credentials = $Credential
            $MkdirRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $MkdirRequest.KeepAlive = $true
            
            $MkdirResponse = $MkdirRequest.GetResponse()
            Write-Host "Directory created: $RemoteSubDir"
            $MkdirResponse.Close()
        }
        catch {
            # Directory may already exist, continue
            Write-Host "Directory $RemoteSubDir (may already exist)"
        }
        
        # Recursively upload subdirectory contents
        Upload-FilesToFtp -LocalDirectory $Dir.FullName -RemoteDirectory $RemoteSubDir
    }
}

Write-Host "Starting FTP upload to $FtpServer..."
Write-Host "Local path: $LocalPath"
Upload-FilesToFtp -LocalDirectory $LocalPath -RemoteDirectory ""
Write-Host "Upload complete!" -ForegroundColor Green
