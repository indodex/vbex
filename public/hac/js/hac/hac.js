/**
 * Created by mango.xu on 2017/10/26.
 */
function showToast(str,type){//type:success,info,error
    $.toast({
        text: str,
        position: 'top-center',
        allowToastClose: true,
        icon: type
    })
}