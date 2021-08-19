#define _GNU_SOURCE
#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <time.h>
#include <limits.h>
#include <ctype.h>
#include <unistd.h>
#include <libgen.h>
#include "ht.h"

char *utf8letter(unsigned char *, char *);

int main(int argc, char **argv)
{
    char cmdp[PATH_MAX];
    if (argc < 2) {
        return -1;
    }

    if (readlink("/proc/self/exe", cmdp, PATH_MAX - 1) == -1)
        return -1;

    printf(utf8letter((unsigned char *)argv[1], dirname(cmdp)));
    return 0;
}

char *
utf8letter(unsigned char *inp, char *path)
{
    static struct hashtable *ht = NULL;
    FILE *f;
    static char line[256];
    char filepath[PATH_MAX];
    char key[10], chinStr[10];
    char *p;
    int i;
    unsigned int unicode;
    unsigned char utf8[4];

    sprintf(filepath, "%s/Uni2Pinyin", path); 
    /* read table */
    ht = htcreate(10031);
    f = fopen(filepath, "r");
    if (!f)
        return (char *)inp;
    while (fgets(line, 255, f)) {
        if (line[0] == '#')
            continue;
        if (sscanf(line, "%s %s", key, chinStr) != 2)
            continue;
        chinStr[1] = '\0';
        chinStr[0] = tolower(chinStr[0]);
        for (p = key; *p; p++)
            *p = tolower(*p);
        htset(ht, key, strdup(chinStr));
    }
    fclose(f);
    bzero(utf8, 4 * sizeof (unsigned char));
    for (i = 0; *inp; inp++, i++) {
        if (*inp > 127) {
            memcpy(utf8, inp, 3 * sizeof(unsigned char));
            inp += 2;
            unicode = to_cp((char *)utf8);
            sprintf(key, "%x", unicode);
            p = htget(ht, key);
            if (p == NULL)
               p = "_";
            line[i] = p[0];
        } else
            line[i] = *inp;
        if (!(line[i] >= 'A' && line[i] <= 'Z') &&
            !(line[i] >= 'a' && line[i] <= 'z') &&
            !(line[i] >= '0' && line[i] <= '9'))
            line[i] = '_';
    }
    line[i] = '\0';
    return line;
}
