const api = {
	init:'/api/v1/config/initialize',									//初始化
	getUserInfo:hostUrl + '/api/v1/user/info',							// 获取用户信息
	marketCoins:hostUrl + '/api/v1/market/coins',						// 货币
	balanceUrl:hostUrl + '/api/v1/account/balance',						// 账户余额
	accountUrl:hostUrl + '/api/v1/account/info',						// 账户详细
	depositsAddress:hostUrl + '/api/v1/account/deposits/address',		// 充值地址
	depositsRecords:hostUrl + '/api/v1/wallet/deposit/records',			// 充值日志
	bill:hostUrl + '/api/v1/account/bill',								// 个人账单
	tradeTypes:hostUrl + '/api/v1/market/trade/types',					// 交易类型
	items:hostUrl + '/api/v1/recharge/items',							// 充值选项
	accountEntrust:hostUrl + '/api/v1/account/entrust',					// 委托列表
	marketAll:hostUrl + '/api/v1/market/all',							// 所有交易市场
	entrustTypes:hostUrl + '/api/v1/trades/types',						// 交易类型
	entrustStatus:hostUrl + '/api/v1/trades/status',					// 交易状态
	checkBalance:hostUrl + '/api/v1/hash/checkBalance',					// 查看记录
	record:hostUrl + '/api/v1/hash/record',								// 获取哈希码记录
	userRechargeOrder:hostUrl + '/api/v1/hash/userRechargeOrder',
	systemRechargeOrder:hostUrl + '/api/v1/hash/systemRechargeOrder',
	createHashCode:hostUrl + '/api/v1/recharge/code/created',			// 创建哈希码
	detailHashCode:hostUrl + '/api/v1/recharge/code/show',				// 查看哈希码
	hashDetail:hostUrl + '/api/v1/hash/hashDetail',						// 哈希码记录
	withdrawAddresses:hostUrl + '/api/v1/wallet/withdraw/addresses',	// 获取提现地址
	withdrawRecords:hostUrl + '/api/v1/wallet/withdraw/records',		// 获取提现记录
	withdrawFee:hostUrl + '/api/v1/wallet/withdraw/fee',				// 获取手续费
	depositApply:hostUrl + '/api/v1/wallet/deposit/address',			// 提现申请
	addWithdrawAddress:hostUrl + '/api/v1/wallet/withdraw/addresses',	// 添加提现地址
	getGoogleSecret:hostUrl + '/api/v1/account/getGoogleSecret',		// 获取谷歌验证
	bindGoogleSecret:hostUrl + '/api/v1/account/bindGoogleSecret',		// 绑定谷歌验证
	forgetGoogleSecret:hostUrl + '/api/v1/account/forgetGoogleSecret',	// 找回谷歌验证
	changeGoogleSecret:hostUrl + '/api/v1/account/changeGoogleSecret',  // 修改谷歌验证
	verifyGoogleCode:hostUrl + '/api/v1/account/verifyGoogleCode',		// 谷歌验证
	getWithdrawAddress:hostUrl + '/api/v1/wallet/withdraw/address',		// 获取提现钱包地址
	delWithdrawAddress:hostUrl + '/api/v1/wallet/withdraw/delete',		// 删除钱包接口
	resetTradeCode:hostUrl + '/api/v1/account/resetTradeCode',			// 重置交易安全码
	setTradeCode:hostUrl + '/api/v1/account/setTradeCode',				// 设置交易安全码
	usedHashCode:hostUrl + '/api/v1/recharge/code/used/personal',		// 个人哈希码使用
	checkHashCode:hostUrl + '/api/v1/recharge/code/used/check',			// 哈希码验证
	officialCodeUsed:hostUrl + '/api/v1/recharge/code/used/offical',	// 系统验证码使用
	personalCodeUsed:hostUrl + '/api/v1/recharge/code/used/personal', 	// 个人哈希码使用
	useHashCode:hostUrl + '/api/v1/recharge/code/use',					// 哈希码使用
	resetPassword:hostUrl + '/api/v1/user/password/rest',				// 密码重置
	changeMobile:hostUrl + '/api/v1/user/change/mobile',				// 修改手机号码
	emailSend:hostUrl + '/api/v1/user/email/code',						// 发送邮件验证码 
	emailVerify:hostUrl + '/api/v1/user/email/verify',					// 验证邮件验证码是否正确
	deductible:hostUrl + '/api/v1/account/deductible',					// HAC 手续费减免
	codeSend:hostUrl + '/api/v1/mobile/send/code',						// 验证码发送接口（不需要登录）
	sendCode:hostUrl + '/api/v1/user/send/code',						// 登录用户验证码发送
	checkCode:hostUrl + '/api/v1/mobile/check/code',					// 验证码认证接口
	walletAccount:hostUrl + '/api/v1/wallet/account',					// 钱包资产
	withdrawApply:hostUrl + '/api/v1/wallet/withdraw/apply',			// 提现申请
	uploadIamge:hostUrl + '/api/v1/account/uploadIamge',				// 图片上传
	baseCertification:hostUrl + '/api/v1/account/baseCertification',	// 初级认证
	advancedCertification:hostUrl + '/api/v1/account/advancedCertification',	// 高级认证
	getCerInfo:hostUrl + '/api/v1/account/getCerInfo',					// 获取认证信息
	changeName:hostUrl + '/api/v1/user/change/name',					// 更新姓名
	inviteList:hostUrl + '/api/v1/account/invite',						// 推荐人列表
	inviteRewradsList:hostUrl + '/api/v1/account/rewards',				// 邀请用户奖励列表
	newsList:hostUrl + '/api/v1/article/news/list',						// 新闻列表
	newsContent:hostUrl + '/api/v1/article/news/content',				// 新闻内容
	articleAboutus:hostUrl + '/api/v1/article/aboutus',					// 关于我们
	articleTerms:hostUrl + '/api/v1/article/terms',						// 服务协议
	articlePrivacy:hostUrl + '/api/v1/article/privacy',					// 隐私声明
	articleFees:hostUrl + '/api/v1/article/fees',						// 费率标准
	articleContact:hostUrl + '/api/v1/article/contac',					// 联系我们
	getLengthDepth:hostUrl + '/api/v1/trades/length',					// 市场深度
	getEntrust:hostUrl + '/api/v1/trades/entrust',						// 委托列表
	marketAccount:hostUrl + '/api/v1/market/account/balance',			// 获取用户市场账号
	marketTicker:hostUrl + '/api/v1/market/ticker',						// 最新价格
	tradesEntrust:hostUrl + '/api/v1/trades/doEntrust',					// 委托交易
	tradesPwd:hostUrl + '/api/v1/trades/check/tradeCode',				// 交易密码验证
	tradesEntrustCancel:hostUrl + '/api/v1/trades/cancel',				// 取消委托交易
	tradesDetails:hostUrl + '/api/v1/trades/details',					// 交易详情
	getMarkets:hostUrl + '/api/v1/market/getMarkets',					// 交易详情
	getMarketsCurrency:hostUrl + '/api/v1/market/getMarketsCurrency',
	withdrawConfirm:hostUrl + '/api/v1/withdraw/confirm',				// 提现确认
	sendWithdrawConfirm:hostUrl + '/api/v1/send/withdraw/confirm',		// 提现确认
	getSafeOption:hostUrl + '/api/v1/public/safe/getLoginOption',
	changeLoginSafeOption:hostUrl + '/api/v1/user/changeLoginSafeOption',
	tradeCheckCode:hostUrl + '/api/v1/trades/check/tradeCode',
	retrieveTradcode:hostUrl + '/api/v1/account/retrieveTradcode',		// 找回交易密码
	sendEmailCode:hostUrl + '/api/v1/account/sendEmailCode',	        // 获取验证码
	checkEmailLock:hostUrl + '/api/v1/account/checkEmailLock',	        // 检测锁定
	loginSafeOption:hostUrl + '/api/v1/member/loginSafeOption',			// 登录页获取用户验证选项
	sendVerifyCode:hostUrl + '/api/v1/account/sendVerifyCode',			// 登录页发送验证码
	sendVerifyCodeForLogin:hostUrl + '/api/v1/member/sendVerifyCode',
	changeTradCodeOption:hostUrl + '/api/v1/user/changeTradCodeOption',
	getTradeOption:hostUrl + '/api/v1/public/safe/getTradeOption',
	getWithdrawalOption:hostUrl + '/api/v1/public/safe/getWithdrawalOption',
	changeWithdrawalOption:hostUrl + '/api/v1/user/changeWithdrawalOption',
	userRegist:hostUrl + '/api/register',								//注册用户
	userRegistEmailboxCheck:hostUrl + '/api/verifications',				//注册邮箱验证码发送
	userRegistCodeCheck:hostUrl + '/api/verify/code',					//注册码验证
	userResetEmail:hostUrl + '/api/password/email',						//重置密码邮件发送
	applyRequest:'/api/v1/coin/apply',									//上币申请
	applyList:'/api/v1/coin/list',										//上币申请列表
	applyDetail:'/api/v1/coin/show',									//上币申请详情
	markeQuotation:'api/v2/index/currencies',							//市场动态
	mineSum: 'api/v2/index/mining',										//挖矿收益
}

export default {
	api,
}
