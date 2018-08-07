#!/usr/bin/python

import sys;
import imaplib;
host = 'webmail.nitt.edu';
port = 143;
uname = sys.argv[1];
passwd = sys.argv[2];
conn = imaplib.IMAP4(host,port);
try:
        conn.login(uname,passwd)
        conn.logout()
        exit(0)
except conn.error:
        exit(1)
