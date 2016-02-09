#!/usr/bin/env python

stdout = ['Step 1.1: mapping - single end', 'ok', \
	'Step 1.2: preprocessing', 'ok', \
	'Step 1.3: INDEL realignment', 'ok', \
	'Step 1.4: Mark duplicates', 'ok', \
	'Step 1.5: Quality score recalibration', 'ok', \
	'Step 1.6: Create final result files', 'ok', \
	'Step 2.1: GATK variant calling', 'ok', \
	'Step 2.1.2: GATK variant filtration', 'ok', \
	'Step 2.2: SAMTOOLS variant calling', 'ok', \
	'Step 2.3: VCF postprocessing', \
	'Step 3.1: VCF intersection', \
	'Step 3.2: VCF merge', \
	'Step 3.3: VCF Venn Diagram', \
	'Step 3.4: Concordance table', 'ok', \
	'Step 3.5: Union GATK/SAMTOOLS', 'ok', \
	'Step 4.1: Variant annotation with annovar', 'ok', \
	'Step 4.2: Generate Final Report', 'ok', \
	'Step 4.3: Leave  variant in if at least 1 mapper_gatk says PASS', 'ok', \
	'Step 4.4: Filtering for Homopolymer Bias + physically removing no_gatk_PASS from final report', 'ok', \
	'Step 4.5: Dropping out everything not in CHP2', 'ok', \
	'Step 4.6: Adding COSMICid', 'ok', \
	'Step 5.1: Low frequency variant calling and annotation', 'ok', \
	'Step 5.2: Low frequency variant output - sorting, COSMIC_id in one field', 'ok', \
	'Step 6: Clean up', 'Finished.']

import time
import random
import sys

for out in stdout:
	print out
	sys.stdout.flush() # Ask Milica to add sys.stdout.flush() after each print and to add ok after each step
	#time.sleep(random.randrange(1, 10, 1)*60)
	time.sleep(random.randrange(1, 60, 1))

# chmod +x <script.py> for php exec
# ps -p <pid> -o comm=
