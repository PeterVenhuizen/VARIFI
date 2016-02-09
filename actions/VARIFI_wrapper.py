#!/usr/bin/env python

import sys, getopt
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
	db = MySQLdb.connect('localhost', 'root', 'root', 'VARIFI')
	cursor = db.cursor()
	
	# Get input files for job
	cursor.execute('SELECT read_file, bed_file, extra_file FROM job_info WHERE job_token = "%s" LIMIT 1' % (token))
	jobFiles = FetchOneAssoc
	
	# Run VARIFI
	cmd = 'python dummy_VARIFI.py'
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
					
					sender = 'no_reply@varifi.at'
					receivers = [email]
					message = """From: From varifi.at <%s>
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
	
	try:
		opts, args = getopt.getopt(sys.argv[1:], 't:', ['token='])
	except getopt.GetoptError:
		sys.exit(2)
		
	for opt, arg in opts:
		if opt in ('-t', '--token'):
			token = arg
			
	try:
		run(token)
	except NameError as err:
		print err
		print 'ERROR: Missing job token'
