#!/usr/bin/env python
# -*- coding: utf-8 -*-

import argparse
import subprocess, shlex
import MySQLdb
import smtplib
import time
from VARIFI_functions import FetchOneAssoc

# MySQLdb tutorial
# http://www.tutorialspoint.com/python/python_database_access.htm

def run(token): 
	''' Run the VARIFI pipeline and realtime parse the stdout output. Update
	the MySQL DB for the running job. '''
	
	# Connect to MySQL DB
	db = MySQLdb.connect('gray', 'varifi', 'v4r1f1us3r', 'varifi')
	cursor = db.cursor()
	
	# Get input files for job
	cursor.execute('SELECT read_file, bed_file, hotspot_file FROM job_info WHERE job_token = "%s" LIMIT 1' % (token))
	jobFiles = FetchOneAssoc(cursor)
	
	# Run VARIFI
	cmd = 'python /project/varifi/html/actions/dummy_VARIFI.py'
	'''
	cmd = 'python /home/CIBIV/milica/workspace/mondti/modti/mondti/mondti-pipeline.py \
-r /project/ngs-work/meta/reference/genomes/hg19_human/hg19.fa \
-bwa \
-bt2 \
-ngm \
-l /scratch/varifi_qsub.{0}.log \
-lt 1 \
-v /project/ngs-work/meta/annotations/snps/dbSNP/human_9606/TSI-12163.vcf \
-ampl /project/varifi/html/uploads/{0}/{1} \
/scratch/varifi_qsub.{0}'.format(token, jobFiles['bed_file'])
	'''

	# Set job start time/message
	message = 'time=%s;step=STARTED;message=Your job has been started.' % (time.strftime('%H:%M'))
	sql = 'UPDATE job_info SET progress = CONCAT(IFNULL(progress, ""), "%s") WHERE job_token = "%s"' % (message, token)
	try:
		cursor.execute(sql)
		db.commit()
	except: db.rollback()

	process = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE)
	while True:
		output = process.stdout.readline()
		if output == '' and process.poll() is not None:
			break
		if output:
			# Parse output and update MySQL
			
			if not 'ok' in output:

				#t = time.strftime('%d-%m-%Y %H:%M')
				t = time.strftime('%H:%M')
				if 'ERRORS' in output:
				
					# Parse log file
					
					# Send email
				
					message = 'time=%s;error=%s;|' % (t, output.rstrip())
					
				elif 'Finished' in output:
					message = 'time=%s;finished=%s;' % (t, token)
					
					# Send email
					'''
					# http://www.tutorialspoint.com/python/python_sending_email.htm
					sql = 'SELECT email FROM submitted_jobs WHERE job_token = "%s"' % (token)
					cursor.execute(sql)
					email = cursor.fetchone()[0]
					
					sender = 'no_reply@varifi.cibiv.univie.ac.at'
					receivers = [email]
					message = """From: From varifi.cibiv.univie.ac.at <%s>
					To: To <%s>
					MIME-Version: 1.0
					Content-type: text/html
					Subject: Your VARIFI job finished!
					
					Your results are available for download <a href="http://localhost/VARIFI/results.php?token=%s">here</a>.
					""" % (sender, email, token)
					
					try:
						smtpObj = smtplib.SMTP('localhost')
						smtpObj.sendmail(sender, receivers, message)
					except SMTPException: print "Error: unable to send email"
					'''
					
					# Update MySQL
					sql = 'UPDATE submitted_jobs SET available_until=CURRENT_TIMESTAMP + INTERVAL 1 WEEK, finished=1 WHERE job_token = "%s"' % (token)
					try: 
						cursor.execute(sql)
						db.commit()
					except: db.rollback()
					
					# Move data to /project/varifi/uploads/[token]/
				
				else: 
					message = 'time=%s;step=%s;message=%s;|' % (t, output.split(':')[0], output.rstrip().split(':')[1])
				
				sql = 'UPDATE job_info SET progress = CONCAT(IFNULL(progress, ""), "%s") WHERE job_token = "%s"' % (message, token)
				try:
					cursor.execute(sql)
					db.commit()
				except: db.rollback()
	rc = process.poll()
	
	# Close connection
	db.close()

if __name__ == '__main__':
	
	parser = argparse.ArgumentParser(description="Wrapper around VARIFI pipeline, which starts the pipeline and tracks progress.")
	parser.add_argument('-t', '--token', help="VARIFI unique job token", required=True)
	args = parser.parse_args()
	
	run(args.token)
