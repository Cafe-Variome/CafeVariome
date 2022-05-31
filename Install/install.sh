cwd=$(dirname "$0")

cat $cwd/cv.txt

apacheUser=$(ps -ef | egrep '(httpd|apache2|apache)' | grep -v 'fusionauth' | grep -v `whoami` | grep -v root | head -n1 | awk '{print $1}')

echo "Welcome to Cafe Variome Installer"                                                               
echo "Cafe Variome is produced at BrookesLab, University of Leicester."                                                               
echo ""                                                               
echo "Setting directory permissions..."                                                                   
setfacl -m u:$apacheUser:rwx $cwd/../writable/
setfacl -m u:$apacheUser:rwx $cwd/../writable/logs/
setfacl -m u:$apacheUser:rwx $cwd/../writable/cache/
setfacl -m u:$apacheUser:rwx $cwd/../writable/uploads/
setfacl -m u:$apacheUser:rwx $cwd/../writable/uploads/data/
setfacl -m u:$apacheUser:rwx $cwd/../writable/uploads/icons/
setfacl -m u:$apacheUser:rwx $cwd/../writable/session/
setfacl -m u:$apacheUser:rwx $cwd/../resources/user_interface_index/
setfacl -m u:$apacheUser:rwx $cwd/../upload/
setfacl -m u:$apacheUser:rwx $cwd/../upload/pairings/
setfacl -m u:$apacheUser:rwx $cwd/../upload/UploadData/
echo "Directory permissions set."

