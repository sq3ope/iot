xampp_htdocs=/Applications/XAMPP/xamppfiles/htdocs
project_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

if [ ! -d "$xampp_htdocs/librus" ]; then
  ln -s "$project_dir/src/" "$xampp_htdocs/librus"
fi
./src/create_dummy_files.sh "$xampp_htdocs"
