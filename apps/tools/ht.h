#ifndef HT_H
#define HT_H
#include <syslog.h>
#include <stdlib.h>
#include <string.h>
#include <stdio.h>
#include <limits.h>
#include <time.h>
#include <unistd.h>
#include <signal.h>
#include <inttypes.h>

struct hashentry {
    char *key;
    void *value;
    struct hashentry *next;
};

struct hashtable {
    int size;
    int walk;
    struct hashentry *we;
    struct hashentry **table;
};

struct uStat {
    char ugroup[128];
    int run;
    int pend;
    int susp;
    struct uStat *next;
};

struct slist {
    int num;
    void *data;
    struct slist *next;
};

struct slist_tr {
    struct slist *p;
};

extern struct hashtable *htcreate(int);
extern void *htget(struct hashtable *, char *);
extern void htset(struct hashtable *, char *, void *);
extern void htfree(struct hashtable *);
extern void htstartwalk(struct hashtable *);
extern void *htwalk(struct hashtable *, char **);
extern struct slist *slist_init ();
extern void slist_destroy (struct slist *);
extern int slist_addfirst (struct slist *, void *);
extern void *slist_rmfirst (struct slist *);
extern int slist_addlast (struct slist *, void *);
extern void *slist_rmlast (struct slist *);
extern void slist_start_walk (struct slist *, struct slist_tr *);
extern void *slist_walk (struct slist_tr *);
extern char *to_utf8(const uint32_t);
extern uint32_t to_cp(const char *);
#endif
