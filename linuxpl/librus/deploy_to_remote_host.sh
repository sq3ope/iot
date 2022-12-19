project_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
source "$project_dir/src/config/remote.inc"

rsync -av -e "ssh -p $port" src/ "$username@$host:public_html/librus/"
ssh -p $port "$username@$host" <<EOF
./public_html/librus/create_dummy_files.sh ./public_html
find  ~/public_html/librus -type d -exec chmod 755 {} \;
find ~/public_html/librus -type f -exec chmod 644 {} \;
EOF

