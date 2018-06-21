function showToast(str,type){//type:success,info,error
    $.toast({
        text: str,
        position: 'top-center',
        allowToastClose: true,
        icon: type
    })
}