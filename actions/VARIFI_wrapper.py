#!/usr/bin/env python
# -*- coding: utf-8 -*-

import argparse
import subprocess, shlex
import MySQLdb
import time
import datetime
from VARIFI_functions import FetchOneAssoc

import smtplib
from email.MIMEMultipart import MIMEMultipart
from email.MIMEText import MIMEText
from email.MIMEBase import MIMEBase
from email import encoders

def run(token): 
	''' Run the VARIFI pipeline and realtime parse the stdout output. Update
	the MySQL DB for the running job. '''
	
	# Connect to MySQL DB
	db = MySQLdb.connect('gray', 'varifi', 'v4r1f1us3r', 'varifi')
	cursor = db.cursor()
	
	# Get input files for job
	cursor.execute('SELECT read_file, bed_file, hotspot_file FROM job_info WHERE BINARY job_token = "%s" LIMIT 1' % (token))
	jobFiles = FetchOneAssoc(cursor)
	
	# Run VARIFI
	cmd = 'python /home/CIBIV/milica/workspace/mondti/modti/mondti/mondti-pipeline.py '
	cmd += '-r /project/ngs-work/meta/reference/genomes/hg19_human/hg19.fa '
	cmd += '-bwa '
	cmd += '-bt2 '
	cmd += '-ngm '
	cmd += '-l /scratch/varifi_qsub.{0}/{0}.log '.format(token)
	cmd += '-lt 1 '
	cmd += '-v /project/ngs-work/meta/annotations/snps/dbSNP/human_9606/TSI-12163.vcf '
	cmd += '-ampl /project/varifi/html/uploads/{}/{} '.format(token, jobFiles['bed_file'])
	cmd += '-hotspot /project/varifi/html/uploads/{}/{} '.format(token, jobFiles['hotspot_file']) if len(jobFiles['hotspot_file']) > 0 else ''
	cmd += '/scratch/varifi_qsub.{}/{}'.format(token, jobFiles['read_file'])

	subprocess.call("echo {} >> /project/varifi/html/qsub_error.log".format(cmd), shell=True)

	# Set job start time/message
	message = 'time=%s;step=STARTED;message=Your job has been started.;|' % (time.strftime('%H:%M'))
	sql = 'UPDATE job_info SET progress = CONCAT(IFNULL(progress, ""), "%s") WHERE BINARY job_token = "%s"' % (message, token)
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

				t = time.strftime('%H:%M')
				if 'ERRORS' in output:
			
					# Get submitter email
					sql = 'SELECT email FROM submitted_jobs WHERE BINARY job_token = "%s"' % (token)
					cursor.execute(sql)
					email = cursor.fetchone()[0]
	
					# Send log file				
					from_addr = "error@varifi.cibiv.univie.ac.at"
					to_addr = "peter.venhuizen@univie.ac.at"

					msg = MIMEMultipart()
					msg['From'] = from_addr
					msg['To'] = to_addr
					msg['Subject'] = 'VARIFI job ({}) error'.format(token)
					body = 'Job submitted by {}'.format(email)
					msg.attach(MIMEText(body, 'plain'))

					attachment = open('/scratch/varifi_qsub.{0}/{0}.log'.format(token), 'rb')
					part = MIMEBase('application', 'octet-stream')
					part.set_payload((attachment).read())
					encoders.encode_base64(part)
					part.add_header('Content-Disposition', "attachment; filename= {}.log".format(token))
					msg.attach(part)

					try:
						server = smtplib.SMTP('localhost')
						text = msg.as_string()
						server.sendmail(from_addr, to_addr, text)
						server.quit()
					except SMTPException: pass

                                        # Update MySQL
                                        sql2 = 'UPDATE submitted_jobs SET available_until=CURRENT_TIMESTAMP + INTERVAL 1 WEEK, finished=1 WHERE BINARY job_token = "%s"' % (token)
                                        try:
                                                cursor.execute(sql2)
                                                db.commit()
                                        except: db.rollback()
	
					# DB update message
					message = 'time=%s;error=An error occurred while running your job. The error report has been send to the VARIFI Support Team and they will contact you on how to proceed.;|' % (t)
					
				elif 'Finished' in output:
					message = 'time=%s;finished=%s;' % (t, token)
				
                                        # Update MySQL
                                        sql = 'UPDATE submitted_jobs SET available_until=CURRENT_TIMESTAMP + INTERVAL 1 WEEK, finished=1 WHERE BINARY job_token = "%s"' % (token)
                                        try:
                                                cursor.execute(sql)
                                                db.commit()
                                        except: db.rollback()

	
					# Send email
					sql = 'SELECT email FROM submitted_jobs WHERE BINARY job_token = "%s"' % (token)
					cursor.execute(sql)
					email = cursor.fetchone()[0]
					
					sender = 'no_reply@varifi.cibiv.univie.ac.at'
					receivers = [email]
					next_week = datetime.datetime.now() + datetime.timedelta(days=7)
					expires_on = next_week.strftime("%A %d %B, %H:%M%p")
					
					eMessage = """From: From varifi.cibiv.univie.ac.at <{0}>
To: To {1}
MIME-Version: 1.0
Content-type: text/html
Subject: Your VARIFI job finished!

Your VARIFI job (<strong>{2}</strong>) has finished running.
Your results are available <a href="http://varifi.cibiv.univie.ac.at/job_results.php?token={2}">here</a>, until <strong>{3}</strong>.
<br><br>
Kind regards,
<br><br>
The VARIFI team""".format(sender, email, token, expires_on)
					
					try:
						smtpObj = smtplib.SMTP('localhost')
						smtpObj.sendmail(sender, receivers, eMessage)
						smtpObj.quit()
					except SMTPException: pass
				
				else: 
					message = 'time=%s;step=%s;message=%s;|' % (t, output.split(':')[0], output.rstrip().split(':')[1])
				
				sql = 'UPDATE job_info SET progress = CONCAT(IFNULL(progress, ""), "%s") WHERE BINARY job_token = "%s"' % (message, token)
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
