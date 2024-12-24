#!bin/bash -l
mkdir -p $HOME/.local/bin/
curl "https://github.com/jolicode/castor/releases/latest/download/castor.linux-amd64.phar" -Lfso $HOME/.local/bin/castor && \
    chmod u+x $HOME/.local/bin/castor
