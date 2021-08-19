#
# Copyright (C) 2020 William Lu (williamlu9@gmail.com)
#
CFLAGS=-c -I. -g -O0

all: cn2py
CC=gcc
CN2PY_OBJ=cn2py.o ht.o

OBJS=$(CN2PY_OBJ)

cn2py: $(CN2PY_OBJ)
	$(CC) $(CN2PY_OBJ) $(LIBS) -o $@


.c.o:
	$(CC) $(CFLAGS) $< -o $@

clean:
	rm $(all) $(OBJS)

