#!/usr/bin/env python

import subprocess
import MySQLdb
from VARIFI_functions import FetchOneAssoc

# Connect to MySQL DB
db = MySQLdb.connect('gray', 'varifi', 'v4r1f1us3r', 'varifi')
cursor = db.cursor()

sql = 'SELECT job_token FROM submitted_jobs WHERE available_until < CURRENT_TIMESTAMP AND finished'
cursor.execute(sql)
row = FetchOneAssoc(cursor)
while row is not None:

	subprocess.call('lynx -dump http://varifi.cibiv.univie.ac.at/actions/remove.php?token={} &> /dev/null'.format(row['job_token']), shell=True)
	
	row = FetchOneAssoc(cursor)	# Next row
