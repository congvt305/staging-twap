/*
	@filename	: ion.js
	@filedesc	: Brand Utility Function
	@date  : 2019.01.03
	@author  : ion
*/
(function(){
    var i = {};

    i.init = function (d,pp,p) {
        this.data = d;
        this.perDivPagination = pp || 1;
        this.perScreenContent = p || 99;
        this.param = '';
    }

    i.getTotalPage = function () {
        var totPage = Math.ceil(this.data.length / this.perScreenContent)
        return totPage
    }

    i.getCurPageContent = function (p) {
        var sNum = (p-1)*this.perScreenContent
        var eNum = p*this.perScreenContent
        var curData = [];
        for(var i = sNum ; i < eNum ; i++){
            if(this.data[i]){
                curData.push(this.data[i])
            } else {
                break;
            }
        }
        return curData;
    }

    i.filter = function (k,v) {
        if(!v) {
            return false;
        }
        var tmp = [];
        var tmpArr = []

        if(v.constructor === Array) {
            for(var i = 0 ; i < this.data.length ; i ++) {
                tmpArr = []
                if(this.data[i][k].indexOf(",") > 0){
                    tmpArr = this.data[i][k].split(",")
                }

                if(tmpArr.length > 1) {
                    for(var l = 0 ; l < v.length ; l++){
                        for(var j = 0 ; j < tmpArr.length ; j++) {
                            if(tmpArr[j].trim().toUpperCase() === v[l].trim().toUpperCase()) {
                                tmp.push(this.data[i])
                                break;
                            }
                        }
                    }
                } else {
                    for(var j = 0 ; j < v.length ; j++){
                        if(this.data[i][k].trim().toUpperCase() === v[j].trim().toUpperCase()) {
                            tmp.push(this.data[i])
                        }
                    }
                }
            }
        } else {
            for(var i = 0 ; i < this.data.length ; i ++) {
                tmpArr = []
                if(this.data[i][k].indexOf(",") > 0){
                    tmpArr = this.data[i][k].split(",")
                }

                if(tmpArr.length > 1) {
                    for(var j = 0 ; j < tmpArr.length ; j++) {
                        if(tmpArr[j].trim().toUpperCase() === v.trim().toUpperCase()) {
                            tmp.push(this.data[i])
                            break;
                        }
                    }
                } else {
                    if(this.data[i][k].trim().toUpperCase() === v.trim().toUpperCase()) {
                        tmp.push(this.data[i])
                    }
                }
            }
        }
        this.data = tmp;
    }

i.iopeMenFilter = function (k,v,k1,v1) {
    var tmp = [];
    var tmpArr = []

    for(var i = 0 ; i < this.data.length ; i ++) {
        tmpArr = []
        if(this.data[i][k].trim().toUpperCase() === v.trim().toUpperCase() && this.data[i][k1].trim().toUpperCase() === v1.trim().toUpperCase()) {
            tmp.push(this.data[i])
        }
    }
    this.data = tmp;
}

    i.findData = function (key,value) {
        for(var i = 0 ; i < this.data.length ; i++) {
            if(this.data[i][key] && this.data[i][key] == value) {
                return this.data[i]
            }
        }
    }
	
	i.findIndexByKey = function (k,v){
        for(var i = 0 ; i < this.data.length ; i++) {
            if(this.data[i][k] && this.data[i][k] == v) {
                return i
            }
        }
	}
	
	i.removeJsonObject = function (k,v){
		var tmp = [];
		var idx = -1;
		idx = this.findIndexByKey(k,v);
		if (idx > -1) {
			this.data.splice(idx, 1);
		}
	}
	
	i.getUniqueObjectArray = function (k){
	  var tmp = [];
	  var tmpArr = [];
	  
	  for(var i = 0; i < this.data.length; i++) {
		if(tmpArr.includes(this.data[i][k])){
		  continue;
		} else {
		  tmp.push(this.data[i]);
		  tmpArr.push(this.data[i][k]);
		}
	  }
	  this.data = tmp;
	}
	
    i.setCookie = function (name,value,exDate) {
        var date = new Date();
        var expireDate = exDate || 60*60*1000
        date.setTime(date.getTime() + expireDate);
        document.cookie = name + '=' + value + ';expires=' + date.toUTCString() + ';path=/';
    }

    i.getCookie = function (name) {
        var value = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
        return value? value[2] : null;
    }

    i.getParameter = function (paramName) {
        var returnValue;
        var url = location.href;
        var parameters = (url.slice(url.indexOf('?') + 1, url.length)).split('&');

        for (var i = 0; i < parameters.length; i++) {
            var varName = parameters[i].split('=')[0];
            if (varName.toUpperCase() == paramName.toUpperCase()) {
                returnValue = parameters[i].split('=')[1];
				returnValue = decodeURIComponent(returnValue);
                return this.XSSCheck(returnValue);
            }
        }
    }
	
	i.XSSCheck = function (str){
		str = str.replace(/\<|\>|\"|\'|\%|\;|\(|\)|\&/g,"");
		str = str.replace(/\</g, "&lt;");
		str = str.replace(/\>/g, "&gt;");
		return str;
	}

    i.getParameterEtc = function (paramName) {
        var returnValue;
        var url = location.href;
        var parameters = (url.slice(url.indexOf('?') + 1, url.length)).split('&');

        for (var i = 0; i < parameters.length; i++) {
            var varName = parameters[i].split('=')[0];
            if (varName.toUpperCase() == paramName.toUpperCase()) {
                returnValue = parameters[i].split('=')[1];
				returnValue = decodeURIComponent(returnValue);
                return this.XSSCheckEtc(returnValue);
            }
        }
    }
	
	i.XSSCheckEtc = function (str){
		str = str.replace(/\<|\>|\"|\'|\%|\;|\(|\)/g,"");
		str = str.replace(/\</g, "&lt;");
		str = str.replace(/\>/g, "&gt;");
		return str;
	}    

    i.getListPage = function( catId, defaultUrl ){
        var url = this.getCookie(catId);
        document.location = url ? url : defaultUrl;
    }

    i.setListPage = function( catId ){
        this.setCookie(catId, document.location);
    }

    i.groupBy = function (k){
        var arr = {}
        for(var i = 0 ; i < this.data.length ; i++){
            if(!arr[this.data[i][k]]){
                arr[this.data[i][k]] = []
            }
            arr[this.data[i][k]].push(this.data[i])
        }
        return arr
    }

    i.makeParam = function (k,v) {
        this.param = ''
        var newParam = k+'='+v;
        var url = location.href;
        var parameters = (url.slice(url.indexOf('?') + 1, url.length)).split('&');
        if(url.indexOf("?") === -1) {
            parameters = []
        }
        for (var i = 0; i < parameters.length; i++) { // 기존 배열에 있는 값 삭제
            var varName = parameters[i].split('=')[0];
            if (varName.toUpperCase() == k.toUpperCase()) {
                parameters.splice(i,1);
                break;
            }
        }

        parameters.push(newParam); // 기존 배열에 신규값 추가

        for (var i = 0; i < parameters.length; i++) {
            if(i === 0) {
                this.param += '?'
            } else {
                this.param += '&'
            }
            this.param += parameters[i];
        }

        return this.param
    }

    i.removeParam = function(key, sourceURL) {
        var rtn = sourceURL.split("?")[0],
            resParams,
            param,
            params_arr = [],
            queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
        if (queryString !== "") {
            //queryString에 값이 있을경우
            params_arr = queryString.split("&");
            for (var i = params_arr.length - 1; i >= 0; i --) {
                param = params_arr[i].split("=")[0];
                if (param === key) {
                    params_arr.splice(i, 1);
                }
            }
            resParams = params_arr.join("&")+"&";
            return resParams;
        }
    };

    i.platformChk = function() {
        var filter = 'win16|win32|win64|mac|macintel';
        var webChk = '';

        if(navigator.platform){
            if(filter.indexOf(navigator.platform.toLowerCase()) === -1) {
                webChk = 'mobile';
            } else {
                webChk = 'pc';
            }
        }
        return webChk;
    }
    i.serializeObject = function(el) {
        var o = {};
        var a = el.serializeArray();
        $.each(a, function() {
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    }

    i.sortData = function(key,order){
        if(order === "desc") {
            this.data.sort(function (b,a) {
                var aKey = a[key];
                var bKey = b[key];
                if(key == "price" || key =="prdLineSort") {
                    aKey = parseInt(aKey.replace(/[^0-9]/g,""));
                    bKey = parseInt(bKey.replace(/[^0-9]/g,""));
                }
                if (aKey > bKey) {
                    return 1;
                }
                if (aKey < bKey) {
                    return -1;
                }
                return 0;
            })
        } else {
            this.data.sort(function (a,b) {
                var aKey = a[key];
                var bKey = b[key];
                if(key == "price" || key =="prdLineSort") {
                    aKey = parseInt(aKey.replace(/[^0-9]/g,""));
                    bKey = parseInt(bKey.replace(/[^0-9]/g,""));
                }
                if (aKey > bKey) {
                    return 1;
                }
                if (aKey < bKey) {
                    return -1;
                }
                return 0;
            })
        }

    }

    i.getDateRange = function (startDate, endDate) {
        var listDate = []
        var dateMove = new Date(startDate);
        var strDate = startDate;

        if (strDate == endDate) {
            strDate = dateMove.toISOString().slice(0,10);
            listDate.push(strDate);
        } else {
            while (strDate < endDate) {
                strDate = dateMove.toISOString().slice(0, 10);
                listDate.push(strDate);
                dateMove.setDate(dateMove.getDate() + 1);
            }
        }
        return listDate;
    };



    // JSON MERGE FUNCTION
    i.mergeJson = function (jData,oriK,tk) {
        var targetKey = tk || oriK
        var addJsonArr = []
        if(jData.length > 0) {
            for(var k in jData[0]) {
                if (k !== targetKey) {
                    addJsonArr.push(k)
                }
            }

            for(var i = 0 ; i<jData.length ; i++) {
                for(var j = 0 ; j < ion.data.length ; j++){
                    if(jData[i][targetKey] === ion.data[j][oriK]) {
                        for (var k = 0; k < addJsonArr.length; k++) {
                            ion.data[j][addJsonArr[k]] = jData[i][addJsonArr[k]]
                        }
                        break;
                    }
                }
            }
        }
    }

    window.ion = i;
})( window );