#include "ht.h"

/* Create a new hashtable. */
struct hashtable *
htcreate(int size) {

    struct hashtable *hashtable = NULL;
    int i;

    if(size < 1) return NULL;

    /* Allocate the table itself. */
    if((hashtable = malloc(sizeof(struct hashtable))) == NULL) {
        return NULL;
    }

    /* Allocate pointers to the head nodes. */
    if( ( hashtable->table = malloc(sizeof(struct hashentry *) * size)) == NULL ) {
        return NULL;
    }
    for(i = 0; i < size; i++) {
        hashtable->table[i] = NULL;
    }

    hashtable->size = size;

    return hashtable;
}

/* Hash a string for a particular hash table. */
static int
ht_hash(struct hashtable *hashtable, char *key) {

    unsigned long int hashval = 0;
    int i = 0;

    /* Convert our string to an integer */
    while(hashval < ULONG_MAX && i < strlen(key)) {
        hashval = hashval << 8;
        hashval += key[i];
        i++;
    }

    return hashval % hashtable->size;
}

/* Create a key-value pair. */
static struct hashentry
*ht_newpair(char *key, void *value) {
    struct hashentry *newpair;

    if((newpair = malloc(sizeof(struct hashentry))) == NULL) {
        return NULL;
    }

    if((newpair->key = strdup(key)) == NULL) {
        return NULL;
    }

    newpair->value = value;

    newpair->next = NULL;

    return newpair;
}

/* Insert a key-value pair into a hash table. */
void
htset(struct hashtable *hashtable, char *key, void *value) {
    int bin = 0;
    struct hashentry *newpair = NULL;
    struct hashentry *next = NULL;
    struct hashentry *last = NULL;

    bin = ht_hash(hashtable, key);

    next = hashtable->table[bin];

    while(next != NULL && next->key != NULL && strcmp(key, next->key) > 0 ) {
        last = next;
        next = next->next;
    }

    /* There's already a pair.  Let's replace that string. */
    if(next != NULL && next->key != NULL && strcmp(key, next->key) == 0 ) {

        next->value = value;

    /* Nope, could't find it.  Time to grow a pair. */
    } else {
        newpair = ht_newpair(key, value);

        /* We're at the start of the linked list in this bin. */
        if(next == hashtable->table[bin]) {
            newpair->next = next;
            hashtable->table[bin] = newpair;

        /* We're at the end of the linked list in this bin. */
        } else if (next == NULL) {
            last->next = newpair;

        /* We're in the middle of the list. */
        } else {
            newpair->next = next;
            last->next = newpair;
        }
    }
}

/* Retrieve a key-value pair from a hash table. */
void *
htget(struct hashtable *hashtable, char *key) {
    int bin = 0;
    struct hashentry *pair;

    bin = ht_hash(hashtable, key);

    /* Step through the bin, looking for our value. */
    pair = hashtable->table[bin];
    while( pair != NULL && pair->key != NULL && strcmp(key, pair->key) > 0 ) {
        pair = pair->next;
    }

    /* Did we actually find anything? */
    if(pair == NULL || pair->key == NULL || strcmp(key, pair->key) != 0) {
        return NULL;

    } else {
        return pair->value;
    }
}

void
htfree(struct hashtable *tab)
{
    int i;

    for (i = 0; i < tab->size; i++) {
        if (tab->table[i] == NULL)
           continue;

        free(tab->table[i]->key);
        free(tab->table[i]);
    }

    free(tab->table);
    free(tab);
}

void
htstartwalk(struct hashtable *table)
{
    if (table == NULL || table->table == NULL)
        return;
    table->walk = 0;
    table->we = table->table[0];
}

void *
htwalk(struct hashtable *table, char **key)
{
    struct hashentry *e;
    if (table == NULL || table->table == NULL) {
        *key = NULL;
        return NULL;
    }

    if (table->we == NULL) {
        for (table->walk++; table->walk < table->size; table->walk++) {
            if (table->table[table->walk] != NULL) {
                e = table->table[table->walk];
                goto found;
            }
        }
        /* no more entry */
        *key = NULL;
        return NULL;
    }
    else    
        e = table->we;

  found:
    table->we = e->next;
    *key = e->key;
    return e->value;
}

struct uStat *new_uStat() {
    struct uStat *u;
    if ((u = calloc(1, sizeof(struct uStat))) == NULL)
        return NULL;
    u->next = u;
    return u;
}

struct uStat *add_uStat(struct uStat *head, char *ugroup, int run, int pend, int susp) {
    struct uStat *u;
    if ((u = malloc(sizeof(struct uStat))) == NULL)
        return NULL;
    if (ugroup && strlen(ugroup) > 127)
        ugroup[127] = '\0';
    strcpy(u->ugroup, ugroup ? ugroup : "");
    u->run = run;
    u->pend = pend;
    u->susp = susp;
    u->next = head->next;
    head->next = u;
    return u;
}

void destroy_uStat(struct uStat *head) {
    struct uStat *orig_head, *next;
    orig_head = head;
    for (head = head->next; head != orig_head;) {
       next = head->next;
       free(head);
       head = next;
    }
    free(orig_head);
}

struct slist *
slist_init ()
{
    struct slist *p;
    if ((p = malloc (sizeof (struct slist))) == NULL)
        return NULL;
    p->num = 0;
    p->next = NULL;
    p->data = NULL;
    return p;
}

void
slist_destroy (struct slist * lh)
{
    if (lh == NULL)
        return;
    while (slist_rmfirst (lh))
        lh->num--;

    free (lh);
    lh = NULL;
}

int
slist_addfirst (struct slist * lh, void *ptr)
{
    struct slist *entry;
    if (lh == NULL)
        return -1;
    if ((entry = calloc (1, sizeof (struct slist))) == NULL)
        return -1;
    entry->data = ptr;
    entry->next = lh->next;
    lh->next = entry;
    lh->num++;
    return 0;
}

void *
slist_rmfirst (struct slist * lh)
{
    struct slist *entry;
    void *ptr;

    if (lh == NULL)
        return NULL;
    if ((entry = lh->next) != NULL) {
        lh->next = entry->next;
        ptr = entry->data;
        lh->num--;
        free (entry);
        return ptr;
    }
    else
        return NULL;
}

typedef struct {
	char mask;    /* char data will be bitwise AND with this */
	char lead;    /* start bytes of current char in utf-8 encoded character */
	uint32_t beg; /* beginning of codepoint range */
	uint32_t end; /* end of codepoint range */
	int bits_stored; /* the number of bits from the codepoint that fits in char */
}utf_t;
 
utf_t * utf[] = {
	/*             mask        lead        beg      end       bits */
	[0] = &(utf_t){0b00111111, 0b10000000, 0,       0,        6    },
	[1] = &(utf_t){0b01111111, 0b00000000, 0000,    0177,     7    },
	[2] = &(utf_t){0b00011111, 0b11000000, 0200,    03777,    5    },
	[3] = &(utf_t){0b00001111, 0b11100000, 04000,   0177777,  4    },
	[4] = &(utf_t){0b00000111, 0b11110000, 0200000, 04177777, 3    },
	      &(utf_t){0},
};
 
/* All lengths are in bytes */
int codepoint_len(const uint32_t cp); /* len of associated utf-8 char */
int utf8_len(const char ch);          /* len of utf-8 encoded char */
 
char *to_utf8(const uint32_t cp);
uint32_t to_cp(const char chr[4]);
 
int codepoint_len(const uint32_t cp)
{
	int len = 0;
        utf_t **u;
	for(u = utf; *u; ++u) {
		if((cp >= (*u)->beg) && (cp <= (*u)->end)) {
			break;
		}
		++len;
	}
	if(len > 4) /* Out of bounds */
		exit(1);
 
	return len;
}
 
int utf8_len(const char ch)
{
	int len = 0;
        utf_t **u;
	for(u = utf; *u; ++u) {
		if((ch & ~(*u)->mask) == (*u)->lead) {
			break;
		}
		++len;
	}
	if(len > 4) { /* Malformed leading byte */
		exit(1);
	}
	return len;
}
 
char *to_utf8(const uint32_t cp)
{
	static char ret[5];
	const int bytes = codepoint_len(cp);
        int i;
 
	int shift = utf[0]->bits_stored * (bytes - 1);
	ret[0] = (cp >> shift & utf[bytes]->mask) | utf[bytes]->lead;
	shift -= utf[0]->bits_stored;
	for(i = 1; i < bytes; ++i) {
		ret[i] = (cp >> shift & utf[0]->mask) | utf[0]->lead;
		shift -= utf[0]->bits_stored;
	}
	ret[bytes] = '\0';
	return ret;
}
 
uint32_t to_cp(const char chr[4])
{
	int bytes = utf8_len(*chr);
	int shift = utf[0]->bits_stored * (bytes - 1);
        int i;
	uint32_t codep = (*chr++ & utf[bytes]->mask) << shift;
 
	for(i = 1; i < bytes; ++i, ++chr) {
		shift -= utf[0]->bits_stored;
		codep |= ((char)*chr & utf[0]->mask) << shift;
	}
 
	return codep;
}
