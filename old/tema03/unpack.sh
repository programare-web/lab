#!/bin/bash

# get all individual assignments from file received as argument
mkdir check/tmp/
unzip $1 -d $2

# moodle sends files with spaces in them, remove it
# if this isn't done, the script borks
find $2 -name "* *" -type f | rename 's/ //g'

# unzip all archives in their corresponding folder
for assignment in $(find $2 -type f); do
    # split the string by _ to weed out the moodle crap
    IFS='_' read -ra PARTS <<< "$assignment"
    
    # split the middle part by - to get the tokens we need
    IFS='-' read -ra PARTS <<< "${PARTS[1]}"

    # we assume the following format: tema03pw-[name]-[first]-[group]
    # create the corresponding folder
    TARGET="$2${PARTS[3]}/${PARTS[1]}-${PARTS[2]}/"
    echo $TARGET
    mkdir -p $TARGET
    unzip $assignment -d $TARGET
    trash $assignment # you can use rm here, trash is safer 
done

