#!/usr/bin/env python

import MySQLdb
from VARIFI_functions import FetchOneAssoc

# Connect to MySQL DB
db = MySQLdb.connect('localhost', 'root', 'root', 'VARIFI')
cursor = db.cursor()

sql = 'SELECT * FROM job_info WHERE job_info.job_token IN (SELECT submitted_jobs.job_token FROM submitted_jobs WHERE available_until > CURRENT_TIMESTAMP AND finished)'
cursor.execute(sql)
row = FetchOneAssoc(cursor)
while row is not None:

	token = row['job_token']
	files = [row['read_file'], row['bed_file'], row['extra_file']]
	
	# Remove job files
	
	# Remove job result files
	
	# Update progress message
	
	row = FetchOneAssoc(cursor)	# Next row
