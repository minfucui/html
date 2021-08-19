#include <stdio.h>
#include <string.h>
int main(int argc, char **argv)  // 用c语言解析特殊格式的数据
{
    FILE *f;
    char line[1024], cmd[1024];
    char *w;
    int n = 0, l, i;
    if (argc < 2) {
        printf("Usage: %s queue_name\n", argv[0]);
        return -1;
    }
    sprintf(cmd, "/usr/sw-mpp/bin/qload -w %s", argv[1]);
    if ((f = popen(cmd, "r")) == NULL) {
        printf("0\n");
        return 0;
    }
    l = strlen(argv[1]);
    while (fgets(line, 1023, f) != NULL) {
        if (strncmp(line, "Error:", 6) == 0)
            break;
        if (strncmp(line, argv[1], l) != 0)
            continue;
        w = strtok(line, " ");
        for (i = 0; w != NULL; w = strtok(NULL, " ")) {
            if (w[0] == '\0' || w[0] == 27)
                continue;
            i++;
            if (i == 10) {
                if(sscanf(w, "%d", &n) != 1)
                    fprintf(stderr, "wrong format %s\n", w);
                break;
            }
        }
    }
    fclose(f);
    printf("%d\n", n);
    return 0;
}
