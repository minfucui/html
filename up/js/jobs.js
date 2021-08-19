function statusClass(status) {  // 作业状态
    var str;
    switch (status) {
        case 'WAIT':
        case 'WSTOP':
        case 'SYSSTOP':
        case 'USRSTOP':
        case 'WAIT':
            //str = 'warning';
            str = '#e08e0b';
            break;
        case 'RUN':
            //str = 'info';
            str = '#00acd6';
            break;
        case 'FINISH':
            //str = 'success';
            str = '#008d4c';
            break;
        default:
            //str = 'danger';
            str = '#d73925';
    }
    return str;
}

function statusStrZh(status) {
    var str;
    switch (status) {
        case 'NULL':
            str = '无';
            break;
        case 'WAIT':
            str = '等待';
            break;
        case 'WSTOP':
            str = '等待时被停止';
            break;
        case 'RUN':
            str = '正在运行';
            break;
        case 'SYSSTOP':
            str = '被系统暂停';
            break;
        case 'USRSTOP':
            str = '运行中被停止';
            break;
        case 'ZOMBIE':
            str = '僵尸';
            break;
        case 'EXIT':
            str = '退出';
            break;
        case 'FINISH':
            str = '完成';
            break;
        case 'UNKOWN':
            str = '未知';
            break;
        case 'ERROR':
            str = '出错';
            break;
        default:
            str = '未知';
    }
    return str;
}

