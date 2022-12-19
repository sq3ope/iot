xampp_htdocs=/Applications/XAMPP/xamppfiles/htdocs
project_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

rsync -av "$project_dir/src/" "$xampp_htdocs/librus/"
./src/create_dummy_files.sh "$xampp_htdocs"
find  "$xampp_htdocs/librus" -type d -exec chmod 755 {} \;
find "$xampp_htdocs/librus" -type f -exec chmod 644 {} \;

echo
echo "Go to http://localhost/librus/plan/tomek.php"

