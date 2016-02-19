<?php
    function generate_token($length) {
	    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	    $n_chars = strlen( $chars );
	    $token = '';
	    for ($i = 0; $i < $length; $i++) {
		    $token .= $chars[ rand( 0, $n_chars-1 ) ];
	    }
	    return $token;
    }

    function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    function rrmdir($path) {
	try {
	    $i = new DirectoryIterator($path);
	    foreach ($i as $f) {
		if ($f->isFile()) {
		    unlink($f->getRealPath());
		} else if (!$f->isDot() && $f->isDir()) {
		    rrmdir($f->getRealPath());
		}
	    }
	    rmdir($path);
	} catch (UnexpectedValueException $ex) { }
    }
    
?>
