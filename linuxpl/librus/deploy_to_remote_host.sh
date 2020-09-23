username=zulu
host=zulu.vdl.pl
port=59184

rsync -av -e "ssh -p 59184" src/ "$username@$host:public_html/librus/"
ssh -p $port "$username@$host" "./public_html/librus/create_dummy_files.sh ./public_html"
