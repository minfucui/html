        var joblist = []
        function myshow(){
            document.getElementById('mydiv').style.width = "120px";
        }
        function myhide(){
            document.getElementById('mydiv').style.width="25px";
        }

        function getalljobs(){  // 获取所有作业的同时，会弹出超过10小时的作业
            ajUrl = "queryAllJob";
            ajData = {
                    "username": "cadmin"  // 写死了，像是个测试
                }
            ajRequest(ajUrl, ajData)
        }

        function alertmessage(tablejson){  // 弹出运行时间超过10小时的作业
            var now = new Date().getTime()
            for(i=0;i<tablejson.length;i++){
                if(tablejson[i].statusString == 'RUN'){
                    starttime = tablejson[i].startTime*1000
                    keepruntime = now - starttime
                    if (keepruntime >36000000){ //10 hours
                        var arr =[]
                        jobid = tablejson[i].jobID.jobID
                        application = tablejson[i].jobSpec.application;
                        startTime = Win10_child.timestampToTime(tablejson[i].submitTime)
                        keepRuntime = Win10_child.DateDifference(keepruntime)
                        arr.push(jobid)
                        arr.push(application)
                        arr.push(startTime)
                        arr.push(keepRuntime)
                        joblist.push(arr)
                    }
                }
            }
            message(joblist)
        }
        
        function ajRequest(url, data) {  // ajaxRequest?跟requestajax有什么区别，多了一个参数url，以及返回值data
            $.ajax({
                type: "post",
                url: "php/aip.php?action=" + url,  // 对作业执行相应的操作接口
                contentType: "application/json",
                data: JSON.stringify(data),
                dataType: "json",
                success: function(d) {
                    if (url == "queryJob" || url == "queryAllJob") {
                        tablejson = d.data;
                        alertmessage(tablejson)

                    } else {
                        selectedJobList = []
                        // location.href = location.href;
                    }
                    if (d.code == "2005") {
                        console.log(d)
                        var href = {};
                        href.url = "./detail.html?id=error&emsg=" + d.message;
                        href.title = "作业错误";
                        console.log(href)
                        Win10_child.open_new_windows(href, true);
                    }
                },
                error: function(e) {
                    // Win10_child.close()
                    // Win10_child.toLogin(e)
                }

            });
        }
        getalljobs()

        function message(joblist){
            if (joblist.length==0) {return false}
            else {
                var modal = document.getElementById('myModal');
                modal.style.display = 'block'
            }
            var tbody = document.getElementById('jobs');  // 表格主体
            for (var i=0;i<joblist.length;i++){
                tr = document.createElement("tr")
                for (var j=0;j<4;j++){
                    td = document.createElement("td")
                    tdtext = document.createTextNode(joblist[i][j])
                    td.appendChild(tdtext)
                    tr.appendChild(td)
                }
                tbody.appendChild(tr)
            }
        }   
        function ok(){
            var modal = document.getElementById('myModal');
            modal.style.display = "none";
        }