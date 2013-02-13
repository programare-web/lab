#!/bin/bash
rm -f tema03.zip
zip -r tema03.zip ./ --exclude js/form-factory-impl* pack.sh \.*
