cat Install/cv.txt

echo "Welcome to Cafe Variome Installer"                                                               
echo "Cafe Variome is produced at BrookesLab, University of Leicester."                                                               
echo ""                                                               
echo "Setting directory permissions..."                                                                   
setfacl -m u:www-data:rwx writable/
setfacl -m u:www-data:rwx writable/logs/
setfacl -m u:www-data:rwx writable/cache/
setfacl -m u:www-data:rwx writable/uploads/
setfacl -m u:www-data:rwx writable/session/
setfacl -m u:www-data:rwx resources/phenotype_lookup_data/
setfacl -m u:www-data:rwx upload/
setfacl -m u:www-data:rwx upload/Pairings/
setfacl -m u:www-data:rwx upload/UploadData/
echo "Directory permissions set."                                                                   

