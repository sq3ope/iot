project_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
source "$project_dir/src/config/remote.inc"

rsync -av -e "ssh -p 59184" src/ "$username@$host:public_html/librus/"
ssh -p $port "$username@$host" "./public_html/librus/create_dummy_files.sh ./public_html"
