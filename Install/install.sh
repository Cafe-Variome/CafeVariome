cat cv.txt

apacheUser=$(ps -ef | egrep '(httpd|apache2|apache)' | grep -v 'fusionauth' | grep -v `whoami` | grep -v root | head -n1 | awk '{print $1}')

echo "Welcome to Cafe Variome Installer"                                                               
echo "Cafe Variome is produced at BrookesLab, University of Leicester."                                                               
echo ""                                                               
echo "Setting directory permissions..."                                                                   
setfacl -m u:$apacheUser:rwx ../writable/
setfacl -m u:$apacheUser:rwx ../writable/logs/
setfacl -m u:$apacheUser:rwx ../writable/cache/
setfacl -m u:$apacheUser:rwx ../writable/uploads/
setfacl -m u:$apacheUser:rwx ../writable/session/
setfacl -m u:$apacheUser:rwx ../resources/phenotype_lookup_data/
setfacl -m u:$apacheUser:rwx ../resources/user_interface_index/
setfacl -m u:$apacheUser:rwx ../upload/
setfacl -m u:$apacheUser:rwx ../upload/pairings/
setfacl -m u:$apacheUser:rwx ../upload/UploadData/
echo "Directory permissions set."                                                                   

