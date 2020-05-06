cat Install/cv.txt

apacheUser = "www-data"

echo "Welcome to Cafe Variome Installer"                                                               
echo "Cafe Variome is produced at BrookesLab, University of Leicester."                                                               
echo ""                                                               
echo "Setting directory permissions..."                                                                   
setfacl -m u:$apacheUser:rwx writable/
setfacl -m u:$apacheUser:rwx writable/logs/
setfacl -m u:$apacheUser:rwx writable/cache/
setfacl -m u:$apacheUser:rwx writable/uploads/
setfacl -m u:$apacheUser:rwx writable/session/
setfacl -m u:$apacheUser:rwx resources/phenotype_lookup_data/
setfacl -m u:$apacheUser:rwx upload/
setfacl -m u:$apacheUser:rwx upload/Pairings/
setfacl -m u:$apacheUser:rwx upload/UploadData/
echo "Directory permissions set."                                                                   

