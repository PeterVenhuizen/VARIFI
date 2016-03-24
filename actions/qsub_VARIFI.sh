#!/bin/bash
#$ -q compute
#$ -cwd
#$ -N varifi_qsub
#$ -e /dev/null
#$ -o /dev/null
#$ -j yes
#$ -l compute
#$ -l hostname=fitch.cibiv.univie.ac.at

DIR=/scratch/varifi_qsub.$1/
mkdir -p $DIR
cd $DIR
cp $2 $DIR
python /project/varifi/html/actions/VARIFI_wrapper.py -t $1

#Move to /project/varifi/html/uploads/$1
mv /scratch/varifi_qsub.$1/* /project/varifi/html/uploads/$1/

#Remove $DIR
cd /scratch
rm -rf /scratch/varifi_qsub.$1

#Remove qsub log file
#rm /project/varifi/html/varifi_qsub.*
exit 0
