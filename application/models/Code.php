<?php
/**
 * class Shared_Model_Code
 *
 * コード管理クラス
 *
 * @package Shared
 * @subpackage Shared_Model
 */
class Shared_Model_Code extends Nutex_Code
{
	const CONTENT_STATUS_INACTIVE       = 0;
	const CONTENT_STATUS_ACTIVE         = 1;

	// 定義リスト
	const DEFINATION_COUNTRY            = 10;
	const DEFINATION_OUR_BUSINESS       = 20;

    const GMO_API_FRESCO                = 1;
    
    const GMO_API_TRANSFER_RESULT_CODE_UNAPPLOVED      = 0;
    const GMO_API_TRANSFER_RESULT_CODE_APPLOVED        = 1;
    const GMO_API_TRANSFER_RESULT_CODE_CANCELED        = 2;
    const GMO_API_TRANSFER_RESULT_CODE_EXPIRED         = 8;

	const DEFINATION_INDUSTORY_CATEGORY                = 30;
	const DEFINATION_INDUSTORY_TYPE                    = 31;
	const DEFINATION_SUPPLY_METHOD                     = 40;
	const DEFINATION_SUPPLY_PRODUCTION_METHOD          = 50;
	const DEFINATION_SUPPLY_PRODUCTION_PURPOSE         = 51;
	const DEFINATION_SUPPLY_SUBCONTRACTIONG_METHOD     = 60;
	const DEFINATION_SUPPLY_SUBCONTRACTIONG_PURPOSE    = 61;
	const DEFINATION_SUPPLY_FIXTURE_USE                = 70; 

    ///////////////////////////////////////////
    // 言語
    ///////////////////////////////////////////
	const LANGUAGE_JP       = 1;
	const LANGUAGE_EN       = 2;
	 
    ///////////////////////////////////////////
    // ユーザー
    ///////////////////////////////////////////
    // ユーザータイプ
    const USER_TYPE_UNREGISTERED       = 0;
    const USER_TYPE_MEMBER             = 1;
    const USER_TYPE_ADMIN              = 10;
    
	// ユーザーステータス
    const USER_STATUS_INACTIVE         = 1;  // 
    const USER_STATUS_ACTIVE           = 10; // 
    const USER_STATUS_COOPERATIVE      = 30;
    
    // 管理者権限
    const ADMIN_AUTHORITY_NONE         = 0; // 管理者権限 なし
    const ADMIN_AUTHORITY_NORMAL       = 1; // 管理者権限 一般
    const ADMIN_AUTHORITY_ADMIN        = 2; // 管理者権限 管理者

    ///////////////////////////////////////////
    // 承認管理ステータス
    ///////////////////////////////////////////
    const APPROVAL_STATUS_INACTIVE       = 0;  // 非アクティブ
    const APPROVAL_STATUS_ACTIVE         = 1;  // アクティブ
    const APPROVAL_STATUS_PENDING        = 10; // 承認待ち 

    ///////////////////////////////////////////
    // 承認
    ///////////////////////////////////////////
	const APPROVAL_STATUS_PENDDING                    = 10; // 承認待ち
	const APPROVAL_STATUS_MOD_REQUEST                 = 20; // 修正依頼
	const APPROVAL_STATUS_REJECTED                    = 30; // 却下
	const APPROVAL_STATUS_APPROVED                    = 40; // 承認済み
	
	const APPROVAL_TYPE_ESTIMATE                      = 10; // 提出見積
	const APPROVAL_TYPE_INVOICE                       = 20; // 請求書発行
	const APPROVAL_TYPE_SUPPLY_COMPETITION            = 30; // 調達管理コンペ
	const APPROVAL_TYPE_COST                          = 40; // 原価計算
	const APPROVAL_TYPE_ORDERFORM                     = 50; // 発注書発行
	const APPROVAL_TYPE_ONLINE_PURCHASE               = 51; // ネット購入委託管理
	const APPROVAL_TYPE_PAYABLE                       = 60; // 請求支払申請
	const APPROVAL_TYPE_PAYABLE_CARD                  = 61; // カード支払申請
	const APPROVAL_TYPE_PAYABLE_MONTHLY               = 62; // 毎月支払管理 新規支払予定
	const APPROVAL_TYPE_PAYABLE_TEMPLATE              = 65; // 毎月支払管理
	
	const APPROVAL_TYPE_ORDER                         = 70; // 受注管理
	const APPROVAL_TYPE_SAMPLE_WASTE                  = 71; // サンプル出荷/在庫破棄
	
	const APPROVAL_TYPE_RECEIVABLE                    = 80; // 入金申請
	const APPROVAL_TYPE_RECEIVABLE_CARD               = 81; // カード返金予定申請
	const APPROVAL_TYPE_RECEIVABLE_MONTHLY            = 82; // 毎月入金管理 新規入金予定
	const APPROVAL_TYPE_RECEIVABLE_TEMPLATE           = 85; // 毎月入金管理
	
	const APPROVAL_TYPE_INVENTORY                     = 90; // 在庫棚卸
	
    ///////////////////////////////////////////
    // 在庫管理
    ///////////////////////////////////////////
    // 在庫管理資材種別
    const ITEM_TYPE_PRODUCT                           = 10; // 商品
    const ITEM_TYPE_BUNDLE                            = 20; // 付属品
    const ITEM_TYPE_INCLUDING                         = 30; // 同梱品
    const ITEM_TYPE_PACKAGE                           = 40; // 梱包資材
    const ITEM_TYPE_MATERIAL                          = 50; // 原料
    const ITEM_TYPE_MANUFACTURING                     = 60; // 製造資材
	
	// 在庫管理資材 引用種別
    const WAREHOUSE_ITEM_TARGET_TYPE_ITEM             = 10;  // 商品
    const WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_PRODUCT   = 20;  // 調達 原料資材
	const WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_FIXTURE   = 30;  // 調達 備品資材
	const WAREHOUSE_ITEM_TARGET_TYPE_OTHER            = 90;  // その他資材	
	
	// 在庫状況
    const STOCK_STATUS_WARNING                        = 10; // 警告値
    const STOCK_STATUS_CAUTION                        = 20; // 注意値
    const STOCK_STATUS_SAFETY                         = 30; // 安全値
    
    
    ///////////////////////////////////////////
    // 棚卸
    ///////////////////////////////////////////
    const INVENTORY_STATUS_DELETED                    = 0;  // 論理削除
    const INVENTORY_STATUS_DRAFT                      = 10; // 入力中
    const INVENTORY_STATUS_PENDING                    = 20; // 承認待ち
    const INVENTORY_STATUS_MOD_REQUEST                = 30; // 修正依頼
    const INVENTORY_STATUS_APPROVED                   = 50; // 実施完了
    
    // 棚卸種別
    const INVENTORY_TYPE_IMPLEMENTATION               = 0;  // 棚卸実施
    const INVENTORY_TYPE_CULCURATE                    = 1;  // 棚卸資産計算
    
      
    ///////////////////////////////////////////
    // アイテム
    ///////////////////////////////////////////
    const CATEGORY_STATUS_INACTIVE     = 0;  // 無効
    const CATEGORY_STATUS_ACTIVE       = 1;  // 有効
    
    const ITEM_STATUS_REMOVE           = 0;  // 削除
    const ITEM_STATUS_INACTIVE         = 1;  // 無効
    const ITEM_STATUS_ACTIVE           = 10; // 有効

    const ITEM_CODE_STATUS_REMOVE      = 0;  // 削除
    const ITEM_CODE_STATUS_INACTIVE    = 1;  // 無効
    const ITEM_CODE_STATUS_ACTIVE      = 10; // 有効

    const ITEM_CODE_BUNDLE_STATUS_REMOVE      = 0;  // 削除
    const ITEM_CODE_BUNDLE_STATUS_INACTIVE    = 1;  // 無効
    const ITEM_CODE_BUNDLE_STATUS_ACTIVE      = 10; // 有効
    
    ///////////////////////////////////////////
    // 商品
    ///////////////////////////////////////////
    // 商品戦略
    const PRODUCT_STRATEGY_SPECIAL           = 10; // 特別戦略品
    const PRODUCT_STRATEGY_DEFAULT           = 20; // 標準設定品
    
    // 商品名区分
    const PRODUCT_NAME_TYPE_SALES            = 1; // 販売商品名
    const PRODUCT_NAME_TYPE_SUPPLY           = 2; // 仕入商品名
    const PRODUCT_NAME_TYPE_SALES_AND_SUPPLY = 3; // 販売・仕入両方の商品名

    // 商品仕入先・製造委託先
    const PRODUCT_RELATIONAL_CONNECTION_TYPE_SUPPLYER       = 10; // 仕入先
    const PRODUCT_RELATIONAL_CONNECTION_TYPE_SUBCONTRACTOR  = 20; // 製造委託先
    	
	// 商品 区分
	/*
	const PRODUCT_CLASS_MATERIAL       = 10; // 原料
	const PRODUCT_CLASS_OWN            = 20; // 自社製造品
	const PRODUCT_CLASS_IMPORT         = 30; // 仕入他社製品
	const PRODUCT_CLASS_OEM_ENTRUST    = 40; // OEM委託品
	const PRODUCT_CLASS_OEM_OUTSOURCE  = 50; // OEM受託品
	const PRODUCT_CLASS_SERVICE        = 60; // 役務
	*/
	const PRODUCT_CLASS_OTHER            = 99; // その他
	const PRODUCT_CLASS_OTHER_TITLE      = 'その他';
	
	// 商品 分類
	/*
	const PRODUCT_CATEGORY_FOOD                = 100; // 食品
	const PRODUCT_CATEGORY_DRINK               = 200; // 飲料
	const PRODUCT_CATEGORY_SUPPLEMENT          = 300; // サプリメント
	const PRODUCT_CATEGORY_APPLIANCE           = 400; // 健康器具
	const PRODUCT_CATEGORY_SANITARY            = 500; // 衛生商品
	const PRODUCT_CATEGORY_EQUIP_ENVIRONMENT   = 600; // 環境設備装置
	const PRODUCT_CATEGORY_EQUIP_PRODUCT       = 700; // 製造設備装置
	*/
	const PRODUCT_CATEGORY_OTHER               = 9999; // その他
    const PRODUCT_CATEGORY_OTHER_TITLE         = 'その他';
                    
	// 販売可能範囲
	const PRODUCT_MARKET_RETAIL_MAIL        = 10; // 小売通販可
	const PRODUCT_MARKET_RETAIL_CLOSED      = 20; // 小売クローズ販売可
	const PRODUCT_MARKET_WHOLESALE_DOMESTIC = 30; // 卸国内販売可
	const PRODUCT_MARKET_WHOLESALE_OVERSEAS = 40; // 卸海外販売可
	const PRODUCT_MARKET_CLOSED             = 50; // 販売不可・使用可
    const PRODUCT_MARKET_UNABLE_BUYING      = 60; // 仕入不可              
	
	// 販売状況
	const PRODUCT_SALES_STATUS_NOPLAN       = 10; // 販売予定無し
	const PRODUCT_SALES_STATUS_PREPARE      = 20; // 販売準備中
	const PRODUCT_SALES_STATUS_ON_SALE      = 30; // 販売中
	const PRODUCT_SALES_STATUS_STOPPING     = 40; // 販売停止中
	const PRODUCT_SALES_STATUS_FINISHED     = 50; // 販売終了
	const PRODUCT_SALES_STATUS_OTHER        = 99; // その他
	
	// 調達方法
	const SUPPLY_METHOD_INDIVIDUAL          = 10; // 個別発注
	const SUPPLY_METHOD_RAKUTEN             = 20; // 楽天
	const SUPPLY_METHOD_AMAZON              = 21; // Amazon
	const SUPPLY_METHOD_MONOTARO            = 22; // モノタロウ
	const SUPPLY_METHOD_YAHOO               = 23; // Yahoo!ショッピング
	const SUPPLY_METHOD_ASKUL               = 24; // アスクル
	
	const SUPPLY_METHOD_OTHER               = 9999; // その他
	const SUPPLY_METHOD_OTHER_TITLE         = 'その他';
	
	// 製品化の手順
	const PRODUCTION_PROCESS_DIRECT                = 10;
	const PRODUCTION_PROCESS_LABEL                 = 20;
	const PRODUCTION_PROCESS_MANUFACTURE           = 30;
	const PRODUCTION_PROCESS_OEM_WITHOUT_SUPPLY    = 40;
	const PRODUCTION_PROCESS_OEM_WITH_SUPPLY       = 50;
	const PRODUCTION_PROCESS_ENTRUST_WITH_SUPPLY   = 60;
	const PRODUCTION_PROCESS_OTHER                 = 99;
	
	// GS卸価格提示条件
	const GS_PRICE_OPEN_DEFAULT             = 10; // ログイン済みバイヤー全てに公開
	const GS_PRICE_OPEN_ESTIMATE            = 20; // 価格提示依頼受け後に標準卸価格を自動提示
	const GS_PRICE_OPEN_COMUNICATION        = 30; // 価格提示依頼受け後に手動回答
	
	// GS標準卸価格
	const GS_PRICE_DEFAULT_ON               = 1; // 標準卸価格あり
	const GS_PRICE_DEFAULT_OFF              = 2; // 標準卸価格なし（全て個別見積）
	
	// GS個別見積可否
	const GS_ESTIMATE_ADVISABILITY_OK                  = 10; // 可
	const GS_ESTIMATE_ADVISABILITY_NG                  = 20; // 不可
	const GS_ESTIMATE_ADVISABILITY_CONSTULTATION_OK    = 30; // 要相談

	// GS上代価格種類
	const PRICE_TYPE_OPEN                   = 10; // オープンプライス
	const PRICE_TYPE_MAKER                  = 20; // メーカー希望小売価格
	const PRICE_TYPE_SALER                  = 30; // 販売企業設定価格
	const PRICE_TYPE_CATALOG                = 40; // カタログ価格
	const PRICE_TYPE_NONE                   = 90; // 上代価格なし

	// 資料種別
    const ITEM_DOCUMENT_TYPE_PUBLIC    = 10;  // 公開資料
    const ITEM_DOCUMENT_TYPE_LIMITED   = 20;  // 限定公開資料
    const ITEM_DOCUMENT_TYPE_PRIVATE   = 30;  // 社内限定資料


    const PACKAGE_TYPE_SIMPLE          = 1;
    const PACKAGE_TYPE_SET             = 2;
    
	const STOCK_ACTION_PLAN_WAREHOUSE  = 10; // 入庫予定
	const STOCK_ACTION_WAREHOUSE       = 20; // 入庫
	const STOCK_ACTION_SHIPMENT        = 30; // 出荷
	const STOCK_ACTION_DEFECTIVE       = 40; // 不良品廃棄
	const STOCK_ACTION_LOST            = 41; // 紛失
	const STOCK_ACTION_DISCARD         = 50; // 一括廃棄
	const STOCK_ACTION_ADJUSTMENT      = 60; // 在庫調整
	const STOCK_ACTION_SHIPMENT_WHOLE  = 70; // 法人出荷
	const STOCK_ACTION_SHIPMENT_SAMPLE = 80; // サンプル出荷
	const STOCK_ACTION_SHIPMENT_USE    = 81; // 他社製造出荷
	const STOCK_ACTION_MATERIAL_USE    = 90; // 自社製造消費

    const STOCK_STATUS_INACTIVE        = 0;  // 在庫履歴無効
    const STOCK_STATUS_ACTIVE          = 1;  // 在庫履歴有効

    ///////////////////////////////////////////
    // 資料
    ///////////////////////////////////////////  
    const MATERIAL_ITEM_TYPE_PRODUCT                   = 1; // 商品
    const MATERIAL_ITEM_TYPE_SUPPLY_PRODUCT            = 2; // 原料・製品
    const MATERIAL_ITEM_TYPE_SUPPLY_PRODUCTION         = 3; // 製造加工委託 
    const MATERIAL_ITEM_TYPE_SUPPLY_SUBCONTRACTING     = 4; // 業務委託
    const MATERIAL_ITEM_TYPE_SUPPLY_FIXTURE            = 5; // 備品・資材 
    
    // 資料形式
    const MATERIAL_TYPE_ESTIMATE                       = 1; // 見積
    const MATERIAL_TYPE_DOCUMENT                       = 2; // 資料
	
	const MATERIAL_SHARED_STATUS_ADDED                 = 1;  // 追加済み
	const MATERIAL_SHARED_STATUS_ACTIVE                = 10; // 共有済み
	const MATERIAL_SHARED_STATUS_INACTIVE              = 0;  // 共有解除
	
	// 資料共有ステータス
	const MATERIAL_STATUS_AVAILABLE                    = 1;  // 有効
	const MATERIAL_STATUS_PUBLISHING_STOPPED           = 2;  // 配布終了
	const MATERIAL_STATUS_DELETED                      = 0;  // 削除済み
	
    ///////////////////////////////////////////
    // 同梱施策
    ///////////////////////////////////////////
    const INCLUDING_PLAN_STATUS_REMOVE          = 0;  // 同梱施策削除
    const INCLUDING_PLAN_STATUS_ACTIVE          = 1;  // 同梱施策有効 
    const INCLUDING_PLAN_STATUS_INACTIVE        = 2;  // 同梱施策無効 
       
    const INCLUDING_PLAN_TERM_TYPE_ORDER        = 1;  // 注文日
    const INCLUDING_PLAN_TERM_TYPE_SHIPPING     = 2;  // 出荷日
    
    const INCLUDING_PLAN_CONDITION_TYPE_ORDER_ALL                  = 1;  // 全ての注文
    const INCLUDING_PLAN_CONDITION_TYPE_ORDER_ITEM                 = 2;  // 特定の商品
    const INCLUDING_PLAN_CONDITION_TYPE_SINGLE_ORDER               = 3;  // 単発注文
    const INCLUDING_PLAN_CONDITION_TYPE_SUBSCRIPTION_ORDER         = 4;  // 定期注文
    
    const INCLUDING_PLAN_CONDITION_ITEM_ORDER_TIME_CURRENT         = 1; // 今回の注文で
    const INCLUDING_PLAN_CONDITION_ITEM_ORDER_TIME_PAST            = 2; // 過去の注文で
    const INCLUDING_PLAN_CONDITION_ITEM_ORDER_TIME_CURRENT_PAST    = 3; // 過去＋今回の注文で
    
    const INCLUDING_PLAN_CONDITION_ITEM_PURCHASED       = 1;  // 購入している
    const INCLUDING_PLAN_CONDITION_ITEM_UNPURCHASED     = 2;  // 購入していない
    
    const INCLUDING_PLAN_ITEM_UNIQUE     = 1; // 同梱をまとめる
    const INCLUDING_PLAN_ITEM_NOT_UNIQUE = 2; // 同梱をまとめない
       
    ///////////////////////////////////////////
    // 出荷管理
    ///////////////////////////////////////////
	const SHIPMENT_STATUS_NEW          = 10; // 新規注文
	const SHIPMENT_STATUS_INSPECTED    = 20; // 検品済み
	const SHIPMENT_STATUS_HOLDED       = 30; // 保留
	const SHIPMENT_STATUS_SHIPPED      = 40; // 出荷済み
    const SHIPMENT_STATUS_RETURNED     = 50; // 返品
	const SHIPMENT_STATUS_CANCELED     = 60; // キャンセル
	const SHIPMENT_STATUS_DELETED      = 99; // 削除
	
	const ORDER_ITEM_STATUS_ACTIVE     = 1;  // アクティブ
	const ORDER_ITEM_STATUS_DELETED    = 0;  // 削除
	
    const ORDER_IMPORT_FORMAT_STATUS_REMOVE      = 0;  // 注文取込フォーマット削除
    const ORDER_IMPORT_FORMAT_STATUS_ACTIVE      = 1;  // 注文取込フォーマット有効 
    
    const ORDER_FROM_SITE_OTHER                        = 0;  // その他
    const ORDER_FROM_SITE_GOOSCA                       = 1;  // GOOSCA
    const ORDER_FROM_SITE_HC                           = 2;  // HC
    const ORDER_FROM_SITE_TYPE_RAKUTEN                 = 10; // 楽天
    const ORDER_FROM_SITE_TYPE_YAHOO                   = 20; // Yahoo!
    const ORDER_FROM_SITE_TYPE_AMAZON                  = 30; // Amazon
    
	const PAYMENT_TYPE_BANK                      = 10; // 銀行振り込み
	const PAYMENT_TYPE_RAKUTEN_BANK              = 11; // 楽天バンク決済
	const PAYMENT_TYPE_CREDIT                    = 20; // クレジットカード
	const PAYMENT_TYPE_CASH_ON_DELIVERY          = 30; // 代金引換
	const PAYMENT_TYPE_NP_DEFERRED               = 40; // NP後払い
	const PAYMENT_TYPE_NP_DEFERRED_WIZ           = 50; // NP後払いwiz
	
	const PAYMENT_TYPE_NP_DEFERRED_ATODINE       = 55; // アトディーネ後払い
	
	const PAYMENT_TYPE_DENSAN                    = 60; // 電算システム
	const PAYMENT_TYPE_SAGAWA_DEFERRED           = 70; // SAGAWA後払い
	const PAYMENT_TYPE_GMO_DEFERRED              = 80; // GMO後払い
	const PAYMENT_TYPE_GMO_DEFERRED_INCLUDING    = 90; // GMO後払い同梱
	const PAYMENT_TYPE_NISSEN_DEFERRED           = 100; // ニッセン後払い
	const PAYMENT_TYPE_NISSEN_DEFERRED_INCLUDING = 110; // ニッセン後払い同梱
	const PAYMENT_TYPE_ATO_BARAI_COM             = 120; // 後払いドットコム
	const PAYMENT_TYPE_YAMATO_DEFERRED           = 130; // ヤマト後払い
	const PAYMENT_TYPE_YAMATO_DEFERRED_INCLUDING = 140; // ヤマト後払い同梱
	const PAYMENT_TYPE_OTHER_PREPAYMENT          = 150; // その他発払い
	const PAYMENT_TYPE_OTHER_DEFERRED            = 160; // その他着払い
	const PAYMENT_TYPE_RAKUTEN                   = 170; // その他(楽天)
	const PAYMENT_TYPE_AMAZON                    = 171; // その他(Amazon)
	const PAYMENT_TYPE_YAHOO                     = 172; // その他(Yahoo!) 
	const PAYMENT_TYPE_GOOSA                     = 173; // その他(goosa)
	const PAYMENT_TYPE_GOOSCA                    = 174; // その他(GOOSCA)
	
	
	const DELIVERY_TYPE_YAMATO                   = 10; // 宅急便
	const DELIVERY_TYPE_YAMATO_COMPACT           = 20; // 宅急便コンパクト
	const DELIVERY_TYPE_YAMATO_COOL              = 30; // クール宅急便
	const DELIVERY_TYPE_YAMATO_COOL_REFRIGERATED = 40; // クール宅急便(冷蔵)
	const DELIVERY_TYPE_YAMATO_POST              = 50; // ネコポス
	const DELIVERY_TYPE_YAMATO_DM                = 60; // クロネコDM便
	const DELIVERY_TYPE_SAGAWA                   = 70; // 飛脚宅配便
	const DELIVERY_TYPE_SAGAWA_COOL              = 80; // 飛脚クール便
	const DELIVERY_TYPE_SAGAWA_MAIL              = 90; // 飛脚メール便
	const DELIVERY_TYPE_JPOST_YU_PACK            = 100; // ゆうパック
	const DELIVERY_TYPE_JPOST_YU_MAIL            = 110; // ゆうメール
	const DELIVERY_TYPE_JPOST_YU_PACKET          = 120; // ゆうパケット
	const DELIVERY_TYPE_JPOST_CHILLED            = 130; // チルドゆうパック
	const DELIVERY_TYPE_INCLUDING                = 140; // 同梱
	const DELIVERY_TYPE_OTHER                    = 200; // その他

	const SHIPMENT_WHOLESALE_TYPE_ORDER          = 10; // 受注
	const SHIPMENT_WHOLESALE_TYPE_SAMPLE         = 20; // サンプル出荷
	
	const SHIPMENT_WHOLESALE_STATUS_NEW          = 0;  // 未出荷
	const SHIPMENT_WHOLESALE_STATUS_HOLDED       = 30; // 保留
	const SHIPMENT_WHOLESALE_STATUS_SHIPPED      = 40; // 出荷済み
	const SHIPMENT_WHOLESALE_STATUS_CANCELED     = 60; // キャンセル
	const SHIPMENT_WHOLESALE_STATUS_DELETED      = 99; // 削除
		
    ///////////////////////////////////////////
    // 取引先
    ///////////////////////////////////////////
    // ログ種別
    const CONNECTION_LOG_TYPE_IMPORT             = 10; // 新規取込 
    const CONNECTION_LOG_TYPE_IMPORT_DEPARTMENT  = 20; // 部署新規取込
    const CONNECTION_LOG_TYPE_IMPORT_STAFF       = 30; // 担当者新規取込
    const CONNECTION_LOG_TYPE_IMPORT_STAFF_UPDATE = 31; // 担当者更新取込
    const CONNECTION_LOG_TYPE_CREATE             = 40; // 新規登録
    const CONNECTION_LOG_TYPE_CREATE_DEPARTMENT  = 50; // 部署新規登録
   	const CONNECTION_LOG_TYPE_UPDATE_BASIC       = 50; // 基本情報更新
	const CONNECTION_LOG_TYPE_UPDATE_STAFF       = 60; // 担当者情報更新

	// ステータス
    const CONNECTION_STATUS_REMOVE               = 0;  // 削除
    const CONNECTION_STATUS_IMPORTED             = 1;  // 取込済
    const CONNECTION_STATUS_ACTIVE               = 10; // 有効


	// 担当者ステータス
    const CONNECTION_STAFF_STATUS_REMOVE        = 0;  // 削除
    const CONNECTION_STAFF_STATUS_OLD           = 1;  // 旧情報
    const CONNECTION_STAFF_STATUS_RETIRED       = 2;  // 退社
    const CONNECTION_STAFF_STATUS_ACTIVE        = 10; // 有効
    
    // 種別
    const CONNECTION_TYPE_COMPANY                = 10; // 法人
    const CONNECTION_TYPE_PERSONAL_BUSINESS      = 20; // 個人事業
    const CONNECTION_TYPE_PRE_OPEN               = 30; // 開業準備前
	const CONNECTION_TYPE_GENERAL_PUBLIC         = 40; // 一般人
	
	// 当社取引関係
	const RELATION_TYPE_PRODUCT_CUSTOMER         = 10;
	const RELATION_TYPE_SERVICE_CUSTOMER         = 20;
	const RELATION_TYPE_PRODUCT_SALLER           = 30;
	const RELATION_TYPE_PRODUCT_SUPPLIER         = 40;
	const RELATION_TYPE_SERVICE_SUPPLIER         = 50;
	const RELATION_TYPE_INV_FIN_SOURCE           = 60;
    const RELATION_TYPE_INV_FIN_DESTINATION      = 70;
    const RELATION_TYPE_OTHER                    = 80;
    
	// 主な商談ポジション
	const SALES_RELATION_BUYER                   = 10;
	const SALES_RELATION_SUPPLIER                = 20;
	const SALES_RELATION_SERVICE_CUSTOMER        = 30;
	const SALES_RELATION_SERVICE_PROVIDER        = 40;
    const SALES_RELATION_OTHER                   = 60;
    
    // 会社形態
	const COMPANY_FORM_CORPORATION               = 10;
	const COMPANY_FORM_LIMITED                   = 20;
	const COMPANY_FORM_LLC                       = 30;
	const COMPANY_FORM_GOVERNMENT                = 40;
	const COMPANY_FORM_ORGANIZATION              = 50;
	const COMPANY_FORM_SELF                      = 60;
	const COMPANY_FORM_INDIVIDUAL                = 70;
	const COMPANY_FORM_OVERSEAS                  = 80;
	const COMPANY_FORM_OTHER                     = 90;

    // 課税・免税
	const DUTY                                   = 10;
	const DUTY_FREE                              = 20;

    // 支払方法
    const CONNECTION_PAYMENT_TYPE_BANK                = 10;
    const CONNECTION_PAYMENT_TYPE_BANK_AUTO           = 20;
   	const CONNECTION_PAYMENT_TYPE_CREDIT_CARD         = 30;
   	const CONNECTION_PAYMENT_TYPE_APP                 = 35;
   	const CONNECTION_PAYMENT_TYPE_CVS_ADVANCE         = 40;
   	const CONNECTION_PAYMENT_TYPE_CVS                 = 45;
   	const CONNECTION_PAYMENT_TYPE_DELIVERY            = 50;
   	const CONNECTION_PAYMENT_TYPE_CASH                = 60;
   	const CONNECTION_PAYMENT_TYPE_OTHER               = 70;
	
	// 支払条件
    const CONNECTION_PAYMENT_CONDITION_BASED_DELIVERY = 10;
    const CONNECTION_PAYMENT_CONDITION_BASED_CLAIM    = 20;
   	const CONNECTION_PAYMENT_CONDITION_WITH_ORDER     = 30;
   	const CONNECTION_PAYMENT_CONDITION_AT_TIME        = 40;
   	const CONNECTION_PAYMENT_CONDITION_OTHER          = 50;
	
	const CONNECTION_PAYMENT_CONDITION_THIS_MONTH     = 10;
	const CONNECTION_PAYMENT_CONDITION_NEXT_MONTH     = 20;
	const CONNECTION_PAYMENT_CONDITION_2NEXT_MONTH    = 30;
	
	// 投融資関係
	const INV_FIN_TYPE_INVEST_SOURCE                  = 10;
    const INV_FIN_TYPE_INVEST_DESTINATION             = 20;
	const INV_FIN_TYPE_FINANCE_SOURCE                 = 30;
    const INV_FIN_TYPE_FINANCE_DESTINATION            = 40;
    
    
	// 営業案件管理 発足区分
	const PROGRESS_START_TYPE_A                       = 10;
	const PROGRESS_START_TYPE_B1                      = 20;
	const PROGRESS_START_TYPE_B2                      = 30;
	const PROGRESS_START_TYPE_C                       = 40;
	
	// 営業案件管理 可能性
	const PROGRESS_POSSIBILITY_HIGH                   = 10;
	const PROGRESS_POSSIBILITY_HALF                   = 20;
	const PROGRESS_POSSIBILITY_LOW                    = 30;
	const PROGRESS_POSSIBILITY_UNKNOWN                = 40;
	const PROGRESS_POSSIBILITY_RECIEVED               = 50;
	const PROGRESS_POSSIBILITY_SAMPLE_RECIEVED        = 55;
	const PROGRESS_POSSIBILITY_FAILURE                = 60;
	
	// 営業案件管理 重要度
	const PROGRESS_IMPORTNCE_C1                       = 11;
	const PROGRESS_IMPORTNCE_C2                       = 12;
	const PROGRESS_IMPORTNCE_C3                       = 13;
	const PROGRESS_IMPORTNCE_P1                       = 21;
	const PROGRESS_IMPORTNCE_P2                       = 22;
	const PROGRESS_IMPORTNCE_S1                       = 31;
	const PROGRESS_IMPORTNCE_S2                       = 32;
	const PROGRESS_IMPORTNCE_Z1                       = 41;
	const PROGRESS_IMPORTNCE_Z2                       = 42;
	
    ///////////////////////////////////////////
    // 調達管理
    ///////////////////////////////////////////
    // 調達ステータス
    const SUPPLY_STATUS_DELETED                        = 0;  // 削除
    const SUPPLY_STATUS_CONSIDERING                    = 1;  // 検討中
    const SUPPLY_STATUS_USING                          = 10; // 調達中
    const SUPPLY_STATUS_NOT_USING                      = 20; // 調達不採用
    const SUPPLY_STATUS_FINISHED_USING                 = 30; // 調達終了

    // 仕入・調達先ステータス
    const SUPPLIER_STATUS_CONSIDERING                  = 1;  // 検討中
    const SUPPLIER_STATUS_USING                        = 10; // 採用中
    const SUPPLIER_STATUS_NOT_USING                    = 20; // 不採用
    const SUPPLIER_STATUS_FINISHED_USING               = 30; // 採用終了
    
    // 原料製品 非売用途
    const USE_NOTSALE_COMPONENT                        = 1; // 製造用構成品
    const USE_NOTSALE_ACCESS_INFO                      = 2; // 情報入手のみ
      
    // コンペステータス
    const COMPETITION_STATUS_DELETED                   = 0;  // 削除
    const COMPETITION_STATUS_PROGRESS                  = 10; // 進行中
    const COMPETITION_STATUS_APPROVAL_PENDDING         = 20; // 完了・承認申請中
    const COMPETITION_STATUS_APPROVED                  = 30; // 完了・承認申請済み
	const COMPETITION_STATUS_STOPPED                   = 40; // 途中終了・一時凍結
	
	// レーティング
	const COMPETITION_RATING_A_PLUS_PLUS               = 32; // A++
	const COMPETITION_RATING_A_PLUS                    = 31; // A+
	const COMPETITION_RATING_A                         = 30; // A
	const COMPETITION_RATING_B_PLUS                    = 21; // B+
	const COMPETITION_RATING_B                         = 20; // B
	const COMPETITION_RATING_C                         = 10; // C
	
    ///////////////////////////////////////////
    // 原価計算
    ///////////////////////////////////////////
    const COST_CALC_STATUS_NOT_CREATED                 = 0;  // 原価計算未作成
    const COST_CALC_STATUS_EDITING                     = 10; // 原価計算編集中
    const COST_CALC_STATUS_APPROVAL_PENDDING           = 20; // 原価計算承認申請中
    const COST_CALC_STATUS_APPROVED                    = 30; // 原価計算承認申請済み
	
    const EXPORT_TYPE_INTERNATIONAL_MAIL               = 10; // 国際郵便・宅急便（EMS／クーリエ）
    const EXPORT_TYPE_AIR_TRANSPORT                    = 20; // 輸出 航空輸送
    const EXPORT_TYPE_MARINE_TRANSPORT                 = 30; // 輸出 海上輸送
	const EXPORT_TYPE_OTHER                            = 30; // その他
	
	const PROCESSING_OWN                               = 10; // 自社作業
	const PROCESSING_SUBCONTRACTING                    = 20; // 外部委託工程

	
    ///////////////////////////////////////////
    // 見積
    ///////////////////////////////////////////
    const ESTIMATE_STATUS_DELETED                     = 0;  // 論理削除
    const ESTIMATE_STATUS_DRAFT                       = 10; // 下書き
    const ESTIMATE_STATUS_PENDING                     = 20; // 承認待ち
    const ESTIMATE_STATUS_MOD_REQUEST                 = 30; // 修正依頼
    const ESTIMATE_STATUS_REJECTED                    = 40; // 却下
    const ESTIMATE_STATUS_APPROVED                    = 50; // 承認済み
    const ESTIMATE_STATUS_SUBMITTED                   = 60; // 提出完了
    
    const ESTIMATE_VERSION_STATUS_CLOSED              = 1;  // クローズ
    const ESTIMATE_VERSION_STATUS_MAKING              = 10; // 作成中
    const ESTIMATE_VERSION_STATUS_PENDING             = 20; // 承認待ち
    const ESTIMATE_VERSION_MOD_REQUEST                = 30; // 修正依頼
    const ESTIMATE_VERSION_STATUS_REJECTED            = 40; // 却下
    const ESTIMATE_VERSION_STATUS_APPROVED            = 50; // 承認済み
    const ESTIMATE_VERSION_STATUS_SUBMITTED           = 60; // 提出完了

    ///////////////////////////////////////////
    // 発注
    ///////////////////////////////////////////
    const ORDER_FORM_STATUS_DELETED                   = 0;  // 論理削除
    const ORDER_FORM_STATUS_DRAFT                     = 10; // 下書き
    const ORDER_FORM_STATUS_PENDING                   = 20; // 承認待ち
    const ORDER_FORM_STATUS_MOD_REQUEST               = 30; // 修正依頼
    const ORDER_FORM_STATUS_APPROVED                  = 40; // 承認済み
    const ORDER_FORM_STATUS_SUBMITTED                 = 50; // 発注完了
    const ORDER_FORM_STATUS_CANCELED                  = 60; // 発注キャンセル
    
    const ORDER_FORM_STATUS_NOT_SUBMITTED             = 99; // 発注未完了
    
    const ORDER_FORM_DELIVERIED_STATUS_NONE           = 0;  // 未受取
    const ORDER_FORM_DELIVERIED_STATUS_RECIEVED       = 1;  // 受取済み

    const ORDER_FORM_PAYABLE_BACKLOG                  = 0;  // 支払申請未完了
    const ORDER_FORM_PAYABLE_COMPLETED                = 1;  // 支払申請完了

    ///////////////////////////////////////////
    // ネット購入委託
    ///////////////////////////////////////////
    const ONLINE_PURCHASE_STATUS_DELETED              = 0;  // 論理削除
    const ONLINE_PURCHASE_STATUS_DRAFT                = 10; // 下書き
    const ONLINE_PURCHASE_STATUS_PENDING              = 20; // 承認待ち
    const ONLINE_PURCHASE_STATUS_MOD_REQUEST          = 30; // 修正依頼
    const ONLINE_PURCHASE_STATUS_APPROVED             = 40; // 承認済み
    const ONLINE_PURCHASE_STATUS_CANCEL               = 60; // キャンセル
    
    const ONLINE_PURCHASE_STATUS_NOT_APPROVED         = 99; // 未承認
    
    const ORDER_FORM_TYPE_CREATE                      = 10; // 注文書作成
    const ORDER_FORM_TYPE_UPLOAD                      = 20; // 注文書アップロード
    
    ///////////////////////////////////////////
    // 内税
    ///////////////////////////////////////////
	const INCLUDING_TAX_NO                            = 0; // 税別価格
	const INCLUDING_TAX_YES                           = 1; // 税込価格
 
    ///////////////////////////////////////////
    // 受注
    ///////////////////////////////////////////
	const DIRECT_ORDER_STATUS_DELETED                 = 0;  // 論理削除
    const DIRECT_ORDER_STATUS_DRAFT                   = 1;  // 下書き
    const DIRECT_ORDER_STATUS_PENDING                 = 2;  // 承認待ち
    const DIRECT_ORDER_STATUS_MOD_REQUEST             = 3;  // 修正依頼
    const DIRECT_ORDER_STATUS_APPROVED                = 4;  // 承認済み
    const DIRECT_ORDER_STATUS_INVOICE_COMPLETED       = 5;  // 請求書発行完了
    const DIRECT_ORDER_STATUS_CANCELED                = 6;  // 受注キャンセル
    const DIRECT_ORDER_STATUS_NOT_COMPLETED           = 10; // 請求書発行未完了
    
    
    const SHIPMENT_TIMING_SOON                        = 10; // 承認と同時に出荷指示
	const SHIPMENT_TIMING_AFTER_PAYMENT               = 20; // 入金確認後
	const SHIPMENT_TIMING_DATE_SPECIFICATION          = 35; // 発送日を指定
	const SHIPMENT_TIMING_PENDING                     = 30; // 保留
	const SHIPMENT_TIMING_NONE                        = 40; // 発送なし
	
    const DIRECT_ORDER_STATUS_NO_SHIPPING             = 10; // 発送なし
	const DIRECT_ORDER_STATUS_WAIT_FOR_PAYMENT        = 20; // 入金待ち
	const DIRECT_ORDER_STATUS_SHIPMENT_DIRECTED       = 30; // 出荷指示済み
	const DIRECT_ORDER_STATUS_SHIPPED                 = 40; // 納品済
	const DIRECT_ORDER_STATUS_SHIPMENT_PENDING        = 50; // 出荷保留
	const DIRECT_ORDER_STATUS_NOT_SHIPPED             = 99; // 未出荷
	
	const DELIVERY_AGENT_YAMATO                       = 10; // ヤマト運輸
	const DELIVERY_AGENT_SAGAWA                       = 11; // 佐川急便
	const DELIVERY_AGENT_JP                           = 12; // 日本郵便
	const DELIVERY_AGENT_NITTSU                       = 13; // 日本通運
	const DELIVERY_AGENT_SEINO                        = 14; // 西濃運輸
	
	const DELIVERY_AGENT_EMS                          = 50; // EMS（国際スピード郵便）
	const DELIVERY_AGENT_DHL                          = 51; // DHL
	const DELIVERY_AGENT_FEDEX                        = 52; // FedEX
	const DELIVERY_AGENT_UPS                          = 53; // UPS
	const DELIVERY_AGENT_TNT                          = 54; // TNT
	const DELIVERY_AGENT_OCS                          = 55; // OCS
	const DELIVERY_AGENT_OTHER                        = 99; // その他

    const DELIVERY_COST_US                            = 10; // 当社負担
	const DELIVERY_COST_ORDERER                       = 20; // 発注者負担
	const DELIVERY_COST_SHARE                         = 30; // 一部発注者負担

    const DIRECT_ORDER_INVOICE_BACKLOG                = 0;  // 請求書発行未完了
    const DIRECT_ORDER_INVOICE_COMPLETED              = 1;  // 請求書発行完了
    
    ///////////////////////////////////////////
    // 請求書
    ///////////////////////////////////////////
    const INVOICE_REFERENCE_TYPE_DIRECT_ORDER         = 10; // 受注管理
    const INVOICE_REFERENCE_TYPE_AGREEMENT            = 20; // 契約書
    
    const INVOICE_STATUS_DELETED                      = 0;  // 論理削除
    const INVOICE_STATUS_DRAFT                        = 10; // 下書き
    const INVOICE_STATUS_PENDING                      = 20; // 承認待ち
    const INVOICE_STATUS_MOD_REQUEST                  = 30; // 修正依頼
    const INVOICE_STATUS_APPROVED                     = 50; // 承認済み
    const INVOICE_STATUS_SUBMITTED                    = 60; // 提出完了
    const INVOICE_STATUS_PAYABLED_ADDED               = 70; // 入金予定登録済み
    const INVOICE_STATUS_CANCELED                     = 80; // キャンセル
    
    const INVOICE_STATUS_PAYABLED_NOT_ADDED           = 99; // 入金予定未登録

    const INVOICE_TYPE_CREATE                         = 10; // 請求書作成
    const INVOICE_TYPE_UPLOAD                         = 20; // 請求書アップロード
    
    ///////////////////////////////////////////
    // 設定
    ///////////////////////////////////////////
    // 明細書テンプレートステータス
    const STATEMENT_TEMPLATE_STATUS_REMOVE            = 0;
    const STATEMENT_TEMPLATE_STATUS_ACTIVE            = 1;
    
	// 明細書テンプレートタイプ
	const STATEMENT_TEMPLATE_TYPE_DEFAULT_1           = 11;
	const STATEMENT_TEMPLATE_TYPE_DEFAULT_2           = 12;
	const STATEMENT_TEMPLATE_TYPE_DEFAULT_3           = 13;
	const STATEMENT_TEMPLATE_TYPE_SUBSCRIPTION_1      = 21;
	const STATEMENT_TEMPLATE_TYPE_SUBSCRIPTION_2      = 22;
	const STATEMENT_TEMPLATE_TYPE_SUBSCRIPTION_3      = 23;
	
	// 倉庫ステータス
    const WAREHOUSE_STATUS_REMOVE          = 0;
    const WAREHOUSE_STATUS_ACTIVE          = 1;

    ///////////////////////////////////////////
    // 金融機関・クレジットカード
    ///////////////////////////////////////////
    // 銀行口座登録種別
    const BANK_REGISTERED_TYPE_FASS              = 10; // FASS上
    const BANK_REGISTERED_TYPE_GOOSA_SP          = 20; // goosa SP
    const BANK_REGISTERED_TYPE_GOOSA_BY          = 21; // goosa BY
    const BANK_REGISTERED_TYPE_GOOSCA_SP         = 30; // GOOSCA 店舗
    const BANK_REGISTERED_TYPE_GOOSCA_BY         = 31; // GOOSCA 会員
    
	// 基本振込先銀行選択肢
	const BASIC_BANK_JAPAN_NET                   = 10; // ジャパンネット銀行
	const BASIC_BANK_RAKUTEN                     = 20; // 楽天銀行
	const BASIC_BANK_MUFG                        = 30; // 三菱UFJ銀行
	const BASIC_BANK_SMBC                        = 40; // 三井住友銀行
	const BASIC_BANK_RISONA                      = 50; // りそな銀行
	const BASIC_BANK_MIZUHO                      = 60; // みずほ銀行
	const BASIC_BANK_YUUTYO                      = 70; // ゆうちょ銀行
	const BASIC_BANK_OTHER                       = 99; // その他
	
	// 振込先口座確認ステータス
	const BANK_CONFIRM_STATUS_NONE               = 0;  // 未登録
	const BANK_CONFIRM_STATUS_RENEWALED          = 1;  // 未確認
	const BANK_CONFIRM_STATUS_CONFIRMED          = 10; // 確認済み

	// 預金種別
   	//const BANK_ACCOUNT_TYPE_NORMAL                    = 10;
   	//const BANK_ACCOUNT_TYPE_CHECKING                  = 20;
   	
   	// 銀行口座種別
    const BANK_ACCOUNT_TYPE_GENERAL           = 1; // 普通預金
    const BANK_ACCOUNT_TYPE_CURRENT           = 2; // 当座預金
    const BANK_ACCOUNT_TYPE_TOTAL             = 3; // 総合口座
    const BANK_ACCOUNT_TYPE_FIX_DEPOSIT       = 4; // 定期預金
    const BANK_ACCOUNT_TYPE_SAVING_DEPOSIT    = 5; // 貯蓄預金
    const BANK_ACCOUNT_TYPE_LARGE_DEPOSIT     = 6; // 大口定期預金
    const BANK_ACCOUNT_TYPE_CUMULATIVE_DEPOST = 7; // 積立定期預金
    
    
    ///////////////////////////////////////////
    // 会計・売掛・買掛
    ///////////////////////////////////////////
    // 売掛種別
    const RECEIVABLE_TYPE_INVOICE                    = 10; // 請求書発行分
    const RECEIVABLE_TYPE_SITE_DATA                  = 15; // サイト連動
	const RECEIVABLE_TYPE_MONTHLY                    = 20; // 毎月入金項目
	const RECEIVABLE_TYPE_OTHER                      = 25; // その他入金項目
	const RECEIVABLE_TYPE_HISTORY                    = 30; // 口座履歴から追加
	const RECEIVABLE_TYPE_CARD                       = 40; // カード返金予定
	
	// 売掛申請ステータス
	const RECEIVABLE_STATUS_DELETED                  = 0;  // 論理削除
    const RECEIVABLE_STATUS_DRAFT                    = 10; // 下書き
    const RECEIVABLE_STATUS_PENDING                  = 20; // 承認待ち
    const RECEIVABLE_STATUS_MOD_REQUEST              = 30; // 修正依頼
    const RECEIVABLE_STATUS_APPROVED                 = 50; // 承認済み
    const RECEIVABLE_STATUS_ADDED_FROM_HISTORY       = 60; // 明細から追加

	// 売掛入金ステータス
    const RECEIVABLE_PAYMENT_STATUS_UNRECEIVED       = 0;  // 未入金
    const RECEIVABLE_PAYMENT_STATUS_RECEIVED         = 10; // 入金済
    const RECEIVABLE_PAYMENT_STATUS_CANCELED         = 30; // キャンセル
    
    // 毎月入金管理テンプレート種別
	const RECEIVABLE_TEMPLATE_TYPE_FIXED             = 10; // 固定費用
	const RECEIVABLE_TEMPLATE_TYPE_VARIABLE          = 20; // 毎月変動
    
	// 毎月入金管理承認ステータス
	const RECEIVABLE_TEMPLATE_STATUS_DELETED         = 0;  // 論理削除
    const RECEIVABLE_TEMPLATE_STATUS_DRAFT           = 10; // 下書き
    const RECEIVABLE_TEMPLATE_STATUS_PENDING         = 20; // 承認待ち
    const RECEIVABLE_TEMPLATE_STATUS_MOD_REQUEST     = 30; // 修正依頼
    const RECEIVABLE_TEMPLATE_STATUS_APPROVED        = 50; // 承認済み
    const RECEIVABLE_TEMPLATE_STATUS_FINISHED        = 60; // 毎月入金終了
	const RECEIVABLE_TEMPLATE_STATUS_NOT_APPROVED    = 99; // 未承認
	
    ///////////////////////////////////////////
    // 会計・売掛・買掛
    ///////////////////////////////////////////
    // 採算コード レイアウト 行項目種別
    const ACCOUNT_TOTALING_ROW_TYPE_HEADING          = 10; // 見出し
    const ACCOUNT_TOTALING_ROW_TYPE_TOTAL            = 20; // 合計
    const ACCOUNT_TOTALING_ROW_TYPE_FREE             = 30; // 自由入力
    const ACCOUNT_TOTALING_ROW_TYPE_REFERENCE        = 40; // 引用
    
	// 発注支払申請実施ステータス
    const ORDER_FORM_PAYABLE_STATUS_BACKLOG          = 0; // 申請未完了
	//const ORDER_FORM_PAYABLE_STATUS_CONTINUE       = 20; // 継続中
    const ORDER_FORM_PAYABLE_STATUS_FINISHED         = 1; // 申請完了
      
    // 買掛種別
    const PAYABLE_PAYING_TYPE_INVOICE                = 10; // 請求支払申請
    const PAYABLE_PAYING_TYPE_SITE_DATA              = 15; // サイト連動
    const PAYABLE_PAYING_TYPE_CREDIT_CARD            = 20; // カード支払申請
    const PAYABLE_PAYING_TYPE_MONTHLY                = 30; // 毎月支払管理
    
    // 買掛 支払方法
    const PAYABLE_PAYING_METHOD_BANK                 = 10; // 銀行振込
    const PAYABLE_PAYING_METHOD_PAYMENT_FORM         = 20; // 振込用紙
	const PAYABLE_PAYING_METHOD_CREDIT               = 30; // クレジット
	const PAYABLE_PAYING_METHOD_AUTO                 = 40; // 口座振替
	const PAYABLE_PAYING_METHOD_CASH                 = 50; // 小口現金
	const PAYABLE_PAYING_METHOD_OTHER                = 90; // その他
            
	// 買掛 支払申請ステータス
	const PAYABLE_STATUS_DELETED                     = 0;  // 論理削除
    const PAYABLE_STATUS_DRAFT                       = 10; // 下書き
    const PAYABLE_STATUS_PENDING                     = 20; // 承認待ち
    const PAYABLE_STATUS_MOD_REQUEST                 = 30; // 修正依頼
    const PAYABLE_STATUS_APPROVED                    = 50; // 承認済み
    const PAYABLE_STATUS_ADDED_FROM_HISTORY          = 60; // 明細から追加

	// 買掛 毎月支払管理ステータス
	const PAYABLE_TEMPLATE_STATUS_DELETED            = 0;  // 論理削除
    const PAYABLE_TEMPLATE_STATUS_DRAFT              = 10; // 下書き
    const PAYABLE_TEMPLATE_STATUS_PENDING            = 20; // 承認待ち
    const PAYABLE_TEMPLATE_STATUS_MOD_REQUEST        = 30; // 修正依頼
    const PAYABLE_TEMPLATE_STATUS_APPROVED           = 50; // 承認済み
    const PAYABLE_TEMPLATE_STATUS_FINISHED           = 60; // 毎月支払終了
    
    const PAYABLE_TEMPLATE_STATUS_NOT_APPROVED       = 99; // 未承認
    
	// 買掛 支払ステータス
    const PAYABLE_PAYMENT_STATUS_UNPAID               = 0;  // 未払
    const PAYABLE_PAYMENT_STATUS_PLANNED              = 1;  // 支払予約済
    const PAYABLE_PAYMENT_STATUS_PLANNED_NOT_APPROVED = 2;  // 予約承認待ち
    const PAYABLE_PAYMENT_STATUS_PLANNED_EXPIRED      = 3;  // 承認期限切れ
    const PAYABLE_PAYMENT_STATUS_PAID                 = 10; // 支払済
    const PAYABLE_PAYMENT_STATUS_PENDDING             = 20; // 保留    
	const PAYABLE_PAYMENT_STATUS_OFFSET_AND_CLOSE     = 40; // 相殺完結
	const PAYABLE_PAYMENT_STATUS_OFFSET_AND_PAY       = 50; // 相殺後振込
	const PAYABLE_PAYMENT_STATUS_UNPAID_PENDDING      = 99; // 未払＆保留(検索用)
    const PAYABLE_PAYMENT_STATUS_CANCELED             = 30; // キャンセル

	// 買掛 テンプレート種別
	const PAYABLE_TEMPLATE_TYPE_FIXED                = 10; // 固定費用
	const PAYABLE_TEMPLATE_TYPE_VARIABLE             = 20; // 毎月変動
	
	// 税区分(課税・非課税)
	const TAX_DIVISION_TAXATION                      = 10;
    const TAX_DIVISION_EXEMPTION                     = 20;

    // 銀行口座履歴 割当ステータス
    const BANK_HISTORY_ITEM_STATUS_DELETED           = 99;
	const BANK_HISTORY_ITEM_STATUS_NONE              = 0;
    const BANK_HISTORY_ITEM_STATUS_ATTACHED          = 10;
    
    // 銀行取込フォーマット
	const BANK_IMPORT_FORMAT_NORMAL                  = 1;
    const BANK_IMPORT_FORMAT_JNB                     = 2;
    
    // クレジットカード請求履歴 割当ステータス
	const CARD_HISTORY_ITEM_STATUS_NONE              = 0;
    const CARD_HISTORY_ITEM_STATUS_ATTACHED          = 10;
    
    ///////////////////////////////////////////
    // マニュアル
    ///////////////////////////////////////////
    // マニュアル機密度
    const MANUAL_CONDIDENTIALITY_MOST                = 10; // A 最重要機密
    const MANUAL_CONDIDENTIALITY_IMPORTANT           = 20; // B 重要機密
    const MANUAL_CONDIDENTIALITY_SPECIFIC            = 30; // C 特定機密
    const MANUAL_CONDIDENTIALITY_GENERAL             = 40; // D 一般社内機密
    const MANUAL_CONDIDENTIALITY_ASSOCIATED          = 50; // E 関連社外秘
    
    // コンテンツタイプ
	const MANUAL_CONTENT_TYPE_TEXT                   = 10;
    const MANUAL_CONTENT_TYPE_IMAGE                  = 20;
    const MANUAL_CONTENT_TYPE_FILE                   = 30;
    
    /**
     * @var array
     */
    protected static $_codeStack = array (
        'gmo_api_result_code' => array(
            self::GMO_API_TRANSFER_RESULT_CODE_UNAPPLOVED  => '未承認',
            self::GMO_API_TRANSFER_RESULT_CODE_CANCELED    => 'キャンセル',
            self::GMO_API_TRANSFER_RESULT_CODE_APPLOVED    => '承認済み',
            self::GMO_API_TRANSFER_RESULT_CODE_EXPIRED     => '期限切れ',
        ),
        
	    ///////////////////////////////////////////
	    // 言語
	    ///////////////////////////////////////////
		'language' => array(
            self::LANGUAGE_JP                 => '日本語',
            self::LANGUAGE_EN                 => '英語',
        ),
        
		'yes_no' => array(
            self::CONTENT_STATUS_ACTIVE       => 'あり',
            self::CONTENT_STATUS_INACTIVE     => 'なし',
        ),

		'no_yes' => array(
			self::CONTENT_STATUS_INACTIVE     => 'なし',
            self::CONTENT_STATUS_ACTIVE       => 'あり', 
        ),

        'is_active' => array(
            self::CONTENT_STATUS_ACTIVE       => '有効',
            self::CONTENT_STATUS_INACTIVE     => '無効',
        ),
        
	    ///////////////////////////////////////////
	    // ユーザー
	    /////////////////////////////////////////// 
		'user_type' => array(
            self::USER_TYPE_UNREGISTERED       => '未登録',
            self::USER_TYPE_MEMBER             => '会員',
            self::USER_TYPE_ADMIN              => '管理者',
        ),

        'user_status' => array(
            self::USER_STATUS_ACTIVE           => 'アクティブ',
            self::USER_STATUS_COOPERATIVE      => '協力会社',
            self::USER_STATUS_INACTIVE         => '退職済み(ログイン不可)',
        ),

	    ///////////////////////////////////////////
	    // アイテム
	    ///////////////////////////////////////////
        'category_status' => array(
        	self::CATEGORY_STATUS_ACTIVE       => '有効',
        	self::CATEGORY_STATUS_INACTIVE     => '無効',
        ),
        
        'item_status' => array(
            self::ITEM_STATUS_ACTIVE           => '有効',
            self::ITEM_STATUS_INACTIVE         => '無効',
        ),

		'package_type' => array(
            self::PACKAGE_TYPE_SIMPLE          => '単体商品',
            self::PACKAGE_TYPE_SET             => 'セット商品',
        ),

	    ///////////////////////////////////////////
	    // 在庫管理資材
	    ///////////////////////////////////////////
		// 在庫管理資材種別	
        'item_type_code' => array(
            self::ITEM_TYPE_PRODUCT            => 'product',
            self::ITEM_TYPE_BUNDLE             => 'bundle',
            self::ITEM_TYPE_INCLUDING          => 'including',
            self::ITEM_TYPE_PACKAGE            => 'package',
            self::ITEM_TYPE_MATERIAL           => 'material',
            self::ITEM_TYPE_MANUFACTURING      => 'manufacturing',
        ),
        
        'item_type' => array(
            self::ITEM_TYPE_PRODUCT            => '商品',
            self::ITEM_TYPE_BUNDLE             => '付属品',
            self::ITEM_TYPE_INCLUDING          => '同梱品',
            self::ITEM_TYPE_PACKAGE            => '梱包資材',
            self::ITEM_TYPE_MATERIAL           => '原料',
            self::ITEM_TYPE_MANUFACTURING      => '製造資材',
        ),
        
        'item_type_prefix' => array(
            self::ITEM_TYPE_PRODUCT            => 'PR',
            self::ITEM_TYPE_BUNDLE             => 'BD',
            self::ITEM_TYPE_INCLUDING          => 'IC',
            self::ITEM_TYPE_PACKAGE            => 'PK',
            self::ITEM_TYPE_MATERIAL           => 'MT',
            self::ITEM_TYPE_MANUFACTURING      => 'MF',
        ),        

	    // 在庫管理資材 引用種別
        'warehouse_item_type' => array(
        	self::WAREHOUSE_ITEM_TARGET_TYPE_ITEM             => '商品',
        	self::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_PRODUCT   => '調達 原料・製品',
        	self::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_FIXTURE   => '調達 備品・資材',
        	self::WAREHOUSE_ITEM_TARGET_TYPE_OTHER            => 'その他資材',
        ),
        
	    // 在庫管理資材 引用種別
        'warehouse_item_type_for_select' => array(
        	self::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_PRODUCT   => '調達 原料・製品',
        	self::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_FIXTURE   => '調達 備品・資材',
        	self::WAREHOUSE_ITEM_TARGET_TYPE_OTHER            => 'その他資材',
        ),
        
        'warehouse_item_status' => array(
            self::ITEM_STATUS_ACTIVE           => '有効', // 有効
            self::ITEM_STATUS_INACTIVE         => '管理終了', // 無効
        ),

	    // 在庫状況
        'stock_status' => array(
            self::STOCK_STATUS_WARNING         => '警告値',
            self::STOCK_STATUS_CAUTION         => '注意値',
            self::STOCK_STATUS_SAFETY          => '安全値',
        ),


	    ///////////////////////////////////////////
	    // 棚卸
	    ///////////////////////////////////////////
        'inventory_status' => array(
            self::INVENTORY_STATUS_DELETED         => '論理削除',
            self::INVENTORY_STATUS_DRAFT           => '実施中',
            self::INVENTORY_STATUS_PENDING         => '承認待ち',
            self::INVENTORY_STATUS_MOD_REQUEST     => '修正依頼',
            self::INVENTORY_STATUS_APPROVED        => '実施完了',
        ), 
        
		// 棚卸種別
        'inventory_type' => array(
            self::INVENTORY_TYPE_IMPLEMENTATION    => '棚卸実施',
            self::INVENTORY_TYPE_CULCURATE         => '棚卸資産計算',
        ), 
        
    
	    ///////////////////////////////////////////
	    // 商品
	    ///////////////////////////////////////////
	    // 商品戦略
	    'strategy' => array(
		    self::PRODUCT_STRATEGY_SPECIAL           => '特別戦略品',
		    self::PRODUCT_STRATEGY_DEFAULT           => '標準設定品',
		),

    	// 商品名区分
		'product_name_type' => array(
            self::PRODUCT_NAME_TYPE_SALES              => '販売商品名',
            self::PRODUCT_NAME_TYPE_SUPPLY             => '仕入商品名',
            self::PRODUCT_NAME_TYPE_SALES_AND_SUPPLY   => '販売・仕入両方の商品名',
        ),
		
		// 商品仕入先・製造委託先
		'product_relational_connection' => array(
            self::PRODUCT_RELATIONAL_CONNECTION_TYPE_SUPPLYER       => '仕入先',
            self::PRODUCT_RELATIONAL_CONNECTION_TYPE_SUBCONTRACTOR  => '製造委託先',
        ),

		// 販売可能範囲
		'product_market' => array(
            self::PRODUCT_MARKET_RETAIL_MAIL         => '小売通販可',
            self::PRODUCT_MARKET_RETAIL_CLOSED       => '小売クローズ販売可',
            self::PRODUCT_MARKET_WHOLESALE_DOMESTIC  => '卸国内販売可',
            self::PRODUCT_MARKET_WHOLESALE_OVERSEAS  => '卸海外販売可',
            self::PRODUCT_MARKET_CLOSED              => '販売不可・使用可',
            self::PRODUCT_MARKET_UNABLE_BUYING       => '仕入不可',
        ),
          
		// 販売状況
		'product_sales_status' => array(
            self::PRODUCT_SALES_STATUS_NOPLAN        => '販売予定無し',
            self::PRODUCT_SALES_STATUS_PREPARE       => '販売準備中',
            self::PRODUCT_SALES_STATUS_ON_SALE       => '販売中',
            self::PRODUCT_SALES_STATUS_STOPPING      => '販売停止中',
            self::PRODUCT_SALES_STATUS_FINISHED      => '販売終了',
            self::PRODUCT_SALES_STATUS_OTHER         => 'その他',
        ),

		// 調達方法
		'supply_method' => array(
            self::SUPPLY_METHOD_INDIVIDUAL           => '個別発注',
            self::SUPPLY_METHOD_RAKUTEN              => '楽天',
            self::SUPPLY_METHOD_AMAZON               => 'Amazon',
            self::SUPPLY_METHOD_MONOTARO             => 'モノタロウ',
            self::SUPPLY_METHOD_YAHOO                => 'Yahoo!ショッピング',
            self::SUPPLY_METHOD_ASKUL                => 'アスクル',
            self::SUPPLY_METHOD_OTHER                => 'その他',
        ),

		// 製品化の手順
		'production_process' => array(
            self::PRODUCTION_PROCESS_DIRECT               => '仕入品のまま販売',
            self::PRODUCTION_PROCESS_LABEL                => '仕入調達後ラベルのみ張り替え／貼付',
            self::PRODUCTION_PROCESS_MANUFACTURE          => '原料・構成品を調達して自社加工・充填・製造',
            self::PRODUCTION_PROCESS_OEM_WITHOUT_SUPPLY   => '支給無しでＯＥＭ製造委託',
            self::PRODUCTION_PROCESS_OEM_WITH_SUPPLY      => '原料・構成品を支給してＯＥＭ製造委託',
            self::PRODUCTION_PROCESS_ENTRUST_WITH_SUPPLY  => '原料・構成品を支給して加工委託して製造',
            self::PRODUCTION_PROCESS_OTHER                => 'その他',
        ),
    
    
    
		// GS卸価格提示条件
		'price_open_condition' => array(
            self::GS_PRICE_OPEN_DEFAULT              => 'ログイン済みバイヤー全てに標準卸価格を公開',
            self::GS_PRICE_OPEN_ESTIMATE             => '価格提示依頼受け後に標準卸価格を自動提示',
            self::GS_PRICE_OPEN_COMUNICATION         => '価格提示依頼受け後に手動回答',
	    ),
	    
		// 標準卸価格
		'price_default' => array(
            self::GS_PRICE_DEFAULT_ON                => '標準卸価格あり',
            self::GS_PRICE_DEFAULT_OFF               => '標準卸価格なし（全て個別見積）',
	    ),

		// GS個別見積可否
		'estimate_advisability' => array(
            self::GS_ESTIMATE_ADVISABILITY_OK                   => '対応する',
            self::GS_ESTIMATE_ADVISABILITY_NG                   => '対応しない',
            self::GS_ESTIMATE_ADVISABILITY_CONSTULTATION_OK     => '要相談',
	    ),
	    
		// GS上代価格種類
		'sales_price_type' => array(
            self::PRICE_TYPE_OPEN                    => 'オープンプライス',
            self::PRICE_TYPE_MAKER                   => 'メーカー希望小売価格',
            self::PRICE_TYPE_SALER                   => '販売企業設定価格',
            self::PRICE_TYPE_CATALOG                 => 'カタログ価格',
            self::PRICE_TYPE_NONE                    => '上代価格なし',
        ),
        

		// 入庫アクション 入力
        'stock_add_input' => array(
            self::STOCK_ACTION_PLAN_WAREHOUSE        => '入庫予定',
            self::STOCK_ACTION_WAREHOUSE             => '入庫',
        ),
		
		// 出庫アクション
        'stock_sub_search' => array(
            self::STOCK_ACTION_SHIPMENT              => 'EC出荷',
            self::STOCK_ACTION_DEFECTIVE             => '不良品廃棄',
            self::STOCK_ACTION_LOST                  => '紛失',
            self::STOCK_ACTION_ADJUSTMENT            => '在庫調整',
            self::STOCK_ACTION_SHIPMENT_WHOLE        => '法人出荷',
            self::STOCK_ACTION_SHIPMENT_SAMPLE       => 'サンプル出荷',
            self::STOCK_ACTION_MATERIAL_USE          => '自社製造消費',
            self::STOCK_ACTION_SHIPMENT_USE          => '他社製造出荷',
        ), 
        
        // 出庫アクション 入力
        'stock_sub_input' => array(
            self::STOCK_ACTION_DEFECTIVE             => '不良品廃棄',
            //self::STOCK_ACTION_LOST                  => '紛失',
            self::STOCK_ACTION_ADJUSTMENT            => '在庫調整',
            self::STOCK_ACTION_SHIPMENT_WHOLE        => '法人出荷',
			self::STOCK_ACTION_SHIPMENT_SAMPLE       => 'サンプル出荷',
            self::STOCK_ACTION_MATERIAL_USE          => '自社製造消費',
        ),

        // 出庫アクション 入力
        'transaction_sample_type' => array(
	        self::STOCK_ACTION_SHIPMENT_SAMPLE       => 'サンプル出荷',
	        self::STOCK_ACTION_SHIPMENT_USE          => '他社製造出荷',
            self::STOCK_ACTION_DEFECTIVE             => '不良品廃棄',
            self::STOCK_ACTION_LOST                  => '紛失',
        ),
        
	    ///////////////////////////////////////////
	    // 同梱施策
	    ///////////////////////////////////////////
        'including_plan_status' => array(
            self::INCLUDING_PLAN_STATUS_ACTIVE       => '有効',
            self::INCLUDING_PLAN_STATUS_INACTIVE     => '無効',
        ),

        'including_plan_term_type' => array(
            self::INCLUDING_PLAN_TERM_TYPE_ORDER     => '注文日（デフォルト）',
            self::INCLUDING_PLAN_TERM_TYPE_SHIPPING  => '出荷日',
        ),
    
        'including_plan_condition_type' => array(
            self::INCLUDING_PLAN_CONDITION_TYPE_ORDER_ALL                 => '全ての注文',
            self::INCLUDING_PLAN_CONDITION_TYPE_ORDER_ITEM                => '特定のオーダー',
            self::INCLUDING_PLAN_CONDITION_TYPE_SINGLE_ORDER              => '単発注文',
            self::INCLUDING_PLAN_CONDITION_TYPE_SUBSCRIPTION_ORDER        => '定期注文',
        ),

        'including_plan_condition_item_order_time' => array(
            self::INCLUDING_PLAN_CONDITION_ITEM_ORDER_TIME_CURRENT        => '今回の注文で',
            self::INCLUDING_PLAN_CONDITION_ITEM_ORDER_TIME_PAST           => '過去の注文で',
            self::INCLUDING_PLAN_CONDITION_ITEM_ORDER_TIME_CURRENT_PAST   => '過去＋今回の注文で',
        ),

        'including_plan_condition_item_purchased' => array(
            self::INCLUDING_PLAN_CONDITION_ITEM_PURCHASED                 => '購入している',
            self::INCLUDING_PLAN_CONDITION_ITEM_UNPURCHASED               => '購入していない',

        ),

        'including_plan_item_unique' => array(
            self::INCLUDING_PLAN_ITEM_UNIQUE                              => '同梱をまとめる',
            self::INCLUDING_PLAN_ITEM_NOT_UNIQUE                          => '同梱をまとめない',
        ),

	    ///////////////////////////////////////////
	    // 資料
	    /////////////////////////////////////////// 
	    // 資料形式
		'material_item_type' => array(
			self::MATERIAL_ITEM_TYPE_PRODUCT                   => '商品',
		    self::MATERIAL_ITEM_TYPE_SUPPLY_PRODUCT            => '原料・製品',
		    self::MATERIAL_ITEM_TYPE_SUPPLY_PRODUCTION         => '製造加工委託',
		    self::MATERIAL_ITEM_TYPE_SUPPLY_SUBCONTRACTING     => '業務委託',
		    self::MATERIAL_ITEM_TYPE_SUPPLY_FIXTURE            => '備品・資材', 
	    ),
	    
	    // 資料形式
		'material_type' => array(
			self::MATERIAL_TYPE_ESTIMATE             => '入手見積',
			self::MATERIAL_TYPE_DOCUMENT             => '入手ドキュメント',
		),
		
		// 資料共有ステータス
		'material_status' => array(
			self::MATERIAL_STATUS_AVAILABLE          => '有効',
			self::MATERIAL_STATUS_PUBLISHING_STOPPED => '配布終了',  // ファイルは保存されているが、共有されない
			self::MATERIAL_STATUS_DELETED            => '削除済み',  // ファイルを削除済み
		),
    
	    ///////////////////////////////////////////
	    // 出荷管理
	    ///////////////////////////////////////////
	    'order_import_column'     => array(
            'relational_order_id'      => '注文番号',
            
            'customer_id'              => '顧客番号',
            'is_royal_customer'        => 'ロイヤルカスタマー',
            'order_customer_name'      => '依頼主名',
            'order_customer_name_kana' => '依頼主名カナ',
            'order_zipcode'            => '依頼主郵便番号',
            'order_contry'             => '配送先国名',
			'order_prefecture'         => '依頼主都道府県',
			'order_address1'           => '依頼主住所1',
			'order_address2'           => '依頼主住所2',
			'order_tel'                => '依頼主電話番号',
			'order_email'              => '依頼主メールアドレス',
			'order_sex'                => '依頼主性別',
			'order_birthday'           => '依頼主誕生日',
			
			'delivery_name'            => '配送先名',
			'delivery_name_kana'       => '配送先名カナ',
			'delivery_zipcode'         => '配送先郵便番号',
			'delivery_contry'          => '配送先国名',
			'delivery_prefecture'      => '配送先都道府県',
			'delivery_address1'        => '配送先住所1',
			'delivery_address2'        => '配送先住所2',
			'delivery_tel'             => '配送先電話番号',
			
			'status'                   => '注文ステータス',
			'order_datetime'           => '注文日時',
			'delivery_request_date'    => 'お届け希望日',
			'delivery_request_time'    => 'お届け希望時間',
			'payment_method'           => 'お支払い方法',
			'delivery_method'          => '配送方法',
			
			'charge'                   => '手数料',
			'delivery_fee'             => '送料',
			'discount'                 => '値引額',
			'tax'                      => '消費税',
			'total'                    => '合計金額',
			
			'get_point'                => '付与ポイント',
			'last_point'               => '残ポイント',
			'use_point'                => '使用ポイント',
			
			'product_name'             => '商品名',
			'product_code'             => '商品コード',
			'unit_price'               => '単価',
			'amount'                   => '数量',
			'item_tax_rate'            => '商品税率',
			'item_total'               => '商品小計(税抜)',
			'item_total_with_tax'      => '商品小計(税込)',

			'subscription_count'       => '定期回数',
			'order_count'              => '注文回数',
			
			'message_inside'           => '注文備考',
			'message_delivery'         => 'オプション情報',
			'message_for_data'         => '定期管理メモ',
			
			'message_to_customer_1'    => '自由記入欄（お客様向け1）',
			'message_to_customer_2'    => '自由記入欄（お客様向け2）',
			'message_to_customer_3'    => '自由記入欄（お客様向け3）',
			'message_to_customer_4'    => '自由記入欄（お客様向け4）',
			'message_to_customer_5'    => '自由記入欄（お客様向け5）',
			'message_to_customer_5'    => '自由記入欄（お客様向け5）',
			
			'subscription_id'             => '定期注文ID',
			'is_subscription_first_order' => '定期初回注文',
			
			'order_from_site'             => '注文サイト',
			'jaccs_transaction_id'        => 'ジャックスTransacionID',
			'jaccs_with_package'          => 'ジャックス同梱',
        ),

		// 注文サイト(定期でも使用)
	    'order_site_type' => array(
		    self::ORDER_FROM_SITE_OTHER                   => 'その他',
		    self::ORDER_FROM_SITE_GOOSCA                  => 'GOOSCA',
		    self::ORDER_FROM_SITE_HC                      => 'FHC',
		    self::ORDER_FROM_SITE_TYPE_RAKUTEN            => '楽天',
		    self::ORDER_FROM_SITE_TYPE_YAHOO              => 'Yahoo!',
		    self::ORDER_FROM_SITE_TYPE_AMAZON             => 'Amazon',
	    ),
	    
	    'order_site_type' => array(
		    self::ORDER_FROM_SITE_OTHER                   => 'その他',
		    self::ORDER_FROM_SITE_GOOSCA                  => 'GOOSCA',
		    self::ORDER_FROM_SITE_HC                      => 'FHC',
		    self::ORDER_FROM_SITE_TYPE_RAKUTEN            => '楽天',
		    self::ORDER_FROM_SITE_TYPE_YAHOO              => 'Yahoo!',
		    self::ORDER_FROM_SITE_TYPE_AMAZON             => 'Amazon',
	    ),
	    
        'shipment_status' => array(
            self::SHIPMENT_STATUS_NEW                     => '新規注文',
            self::SHIPMENT_STATUS_INSPECTED               => '検品済み',
            self::SHIPMENT_STATUS_HOLDED                  => '保留',
            self::SHIPMENT_STATUS_SHIPPED                 => '出荷済み',
            self::SHIPMENT_STATUS_RETURNED                => '返品', 
			self::SHIPMENT_STATUS_CANCELED                => 'キャンセル', 
        ),

        'payment_type' => array(
            self::PAYMENT_TYPE_BANK                       => '銀行振り込み',
            self::PAYMENT_TYPE_RAKUTEN_BANK               => '楽天バンク決済',
            self::PAYMENT_TYPE_CREDIT                     => 'クレジットカード',
            self::PAYMENT_TYPE_CASH_ON_DELIVERY           => '代金引換',
            self::PAYMENT_TYPE_NP_DEFERRED                => 'NP後払い',
            self::PAYMENT_TYPE_NP_DEFERRED_WIZ            => 'NP後払いwiz', 
            self::PAYMENT_TYPE_NP_DEFERRED_ATODINE        => 'アトディーネ後払い',
			self::PAYMENT_TYPE_DENSAN                     => '電算システム',
			self::PAYMENT_TYPE_SAGAWA_DEFERRED            => 'SAGAWA後払い',
			self::PAYMENT_TYPE_GMO_DEFERRED               => 'GMO後払い',
			self::PAYMENT_TYPE_GMO_DEFERRED_INCLUDING     => 'GMO後払い同梱',
			self::PAYMENT_TYPE_NISSEN_DEFERRED            => 'スコア後払い',
			self::PAYMENT_TYPE_NISSEN_DEFERRED_INCLUDING  => 'スコア後払い(同梱)',
			self::PAYMENT_TYPE_ATO_BARAI_COM              => '後払いドットコム',
			self::PAYMENT_TYPE_YAMATO_DEFERRED            => 'ヤマト後払い',
			self::PAYMENT_TYPE_YAMATO_DEFERRED_INCLUDING  => 'ヤマト後払い同梱',
			self::PAYMENT_TYPE_OTHER_PREPAYMENT           => 'その他発払い',
			self::PAYMENT_TYPE_OTHER_DEFERRED             => 'その他着払い',
			self::PAYMENT_TYPE_RAKUTEN                    => 'その他(楽天)',
			self::PAYMENT_TYPE_AMAZON                     => 'その他(Amazon)',
			self::PAYMENT_TYPE_YAHOO                      => 'その他(Yahoo!) ',
			self::PAYMENT_TYPE_GOOSA                      => 'その他(goosa)',
			self::PAYMENT_TYPE_GOOSCA                     => 'その他(GOOSCA)',
        ),
        
        'delivery_type' => array(
            self::DELIVERY_TYPE_YAMATO                    => '宅急便',
            self::DELIVERY_TYPE_YAMATO_COMPACT            => '宅急便コンパクト',
            self::DELIVERY_TYPE_YAMATO_COOL               => 'クール宅急便',
            self::DELIVERY_TYPE_YAMATO_COOL_REFRIGERATED  => 'クール宅急便(冷蔵)',
            self::DELIVERY_TYPE_YAMATO_POST               => 'ネコポス', 
			self::DELIVERY_TYPE_YAMATO_DM                 => 'クロネコDM便',
			self::DELIVERY_TYPE_SAGAWA                    => '飛脚宅配便',
			self::DELIVERY_TYPE_SAGAWA_COOL               => '飛脚クール便',
			self::DELIVERY_TYPE_SAGAWA_MAIL               => '飛脚メール便',
			self::DELIVERY_TYPE_JPOST_YU_PACK             => 'ゆうパック',
			self::DELIVERY_TYPE_JPOST_YU_MAIL             => 'ゆうメール',
			self::DELIVERY_TYPE_JPOST_YU_PACKET           => 'ゆうパケット',
			self::DELIVERY_TYPE_JPOST_CHILLED             => 'チルドゆうパック',
			self::DELIVERY_TYPE_INCLUDING                 => '同梱',
			self::DELIVERY_TYPE_OTHER                     => 'その他',
        ),
                
        'delivery_request_time' => array(
            '0812' => '午前中',
            '1416' => '14～16時',
            '1618' => '16～18時',
            '1820' => '18～20時',
            '1921' => '19～21時',
        ),

        'shipment_wholesale_type' => array(   /// 未使用
            self::SHIPMENT_WHOLESALE_TYPE_ORDER           => '受注',
            self::SHIPMENT_WHOLESALE_TYPE_SAMPLE          => 'サンプル出荷',
        ),        

        'shipment_wholesale_status' => array(
            self::SHIPMENT_WHOLESALE_STATUS_NEW           => '未発送',
            self::SHIPMENT_WHOLESALE_STATUS_HOLDED        => '保留',
            self::SHIPMENT_WHOLESALE_STATUS_SHIPPED       => '発送済み',
            self::SHIPMENT_WHOLESALE_STATUS_CANCELED      => 'キャンセル',
        ),
        
        
	    ///////////////////////////////////////////
	    // 取引先
	    ///////////////////////////////////////////
	    // ログ種別
        'connection_log_type' => array(
            self::CONNECTION_LOG_TYPE_IMPORT                 => '新規取込',
            self::CONNECTION_LOG_TYPE_IMPORT_DEPARTMENT      => '部署新規取込',
            self::CONNECTION_LOG_TYPE_IMPORT_STAFF           => '担当者新規取込',
            self::CONNECTION_LOG_TYPE_IMPORT_STAFF_UPDATE    => '担当者更新取込',
            self::CONNECTION_LOG_TYPE_CREATE                 => '新規登録',
            self::CONNECTION_LOG_TYPE_CREATE_DEPARTMENT      => '部署新規登録',
            self::CONNECTION_LOG_TYPE_UPDATE_BASIC           => '基本情報更新',
            self::CONNECTION_LOG_TYPE_UPDATE_STAFF           => '担当者情報更新',
        ),
        
	    // ステータス
        'connection_status' => array(
            self::CONNECTION_STATUS_REMOVE                   => '削除',
            self::CONNECTION_STATUS_IMPORTED                 => '取込済み未完成',
            self::CONNECTION_STATUS_ACTIVE                   => '有効',
        ),
        
	    // 担当者ステータス
        'connection_staff_status' => array(
            self::CONNECTION_STAFF_STATUS_ACTIVE              => '有効',
            self::CONNECTION_STAFF_STATUS_OLD                 => '旧情報',
            self::CONNECTION_STAFF_STATUS_RETIRED             => '退社',
        ),  
    
	    // 種別
        'connection_type' => array(
            self::CONNECTION_TYPE_COMPANY                     => '法人',
            self::CONNECTION_TYPE_PERSONAL_BUSINESS           => '個人事業',
            self::CONNECTION_TYPE_PRE_OPEN                    => '開業準備前',
            self::CONNECTION_TYPE_GENERAL_PUBLIC              => '一般人',
        ),

		// 当社取引関係
        'relation_type' => array(
            self::RELATION_TYPE_PRODUCT_CUSTOMER              => '販売先',
            self::RELATION_TYPE_SERVICE_CUSTOMER              => '役務提供先',
            self::RELATION_TYPE_PRODUCT_SALLER                => '仕入先',
            self::RELATION_TYPE_PRODUCT_SUPPLIER              => '製造加工委託先',
            self::RELATION_TYPE_SERVICE_SUPPLIER              => '役務委託先',
            self::RELATION_TYPE_INV_FIN_SOURCE                => '投融資元',
            self::RELATION_TYPE_INV_FIN_DESTINATION           => '投融資先',
            self::RELATION_TYPE_OTHER                         => 'その他',  
        ),
        
		// 当社に対する先方役割
        'sales_relation' => array(
            self::SALES_RELATION_BUYER                       => 'カスタマー（顧客）',
            self::SALES_RELATION_SUPPLIER                    => 'サプライヤー（仕入・委託先）',
            self::SALES_RELATION_OTHER                       => 'パートナーetc.（協業者・他）',        
        ),
        
	    // 会社形態
        'company_form' => array(
            self::COMPANY_FORM_CORPORATION                   => '株式会社',
            self::COMPANY_FORM_LIMITED                       => '有限会社',
            self::COMPANY_FORM_LLC                           => '合同会社',
            self::COMPANY_FORM_GOVERNMENT                    => '行政機関・学校',
            self::COMPANY_FORM_ORGANIZATION                  => '任意団体・会合',
            self::COMPANY_FORM_SELF                          => '自営業・個人事業者',
            self::COMPANY_FORM_INDIVIDUAL                    => '個人',
            self::COMPANY_FORM_OVERSEAS                      => '海外企業',
            self::COMPANY_FORM_OTHER                         => 'その他',        
        ),
		
		// 課税・免税
		'duty' => array(
            self::DUTY                                          => '課税業者',
            self::DUTY_FREE                                     => '免税業者',     
        ),
        
		// 支払方法
		'connection_payment_type' => array(
            self::CONNECTION_PAYMENT_TYPE_BANK                  => '銀行振込み',
            self::CONNECTION_PAYMENT_TYPE_BANK                  => '銀行振込み',
            self::CONNECTION_PAYMENT_TYPE_BANK_AUTO             => '自動口座引落し',
            self::CONNECTION_PAYMENT_TYPE_CREDIT_CARD           => 'クレジットカード',
            self::CONNECTION_PAYMENT_TYPE_APP                   => '決済アプリ',
            self::CONNECTION_PAYMENT_TYPE_CVS_ADVANCE           => 'コンビニ前払い',
            self::CONNECTION_PAYMENT_TYPE_CVS                   => 'コンビニ後払い',
            self::CONNECTION_PAYMENT_TYPE_DELIVERY              => '代引き',
            self::CONNECTION_PAYMENT_TYPE_CASH                  => '小口現金',
            self::CONNECTION_PAYMENT_TYPE_OTHER                 => 'その他',
        ),
        
		// 支払条件
		'connection_payment_condition' => array(
            self::CONNECTION_PAYMENT_CONDITION_BASED_DELIVERY   => '納入締払い',
            self::CONNECTION_PAYMENT_CONDITION_BASED_CLAIM      => '請求締払い',
            self::CONNECTION_PAYMENT_CONDITION_WITH_ORDER       => '注文時支払（納入前払い）',
            self::CONNECTION_PAYMENT_CONDITION_AT_TIME          => '随時取決め',
            self::CONNECTION_PAYMENT_CONDITION_OTHER            => 'その他',
        ),

		// その他支払条件
		'order_payment_condition' => array(
            self::CONNECTION_PAYMENT_CONDITION_BASED_DELIVERY   => '納入締払い',
            self::CONNECTION_PAYMENT_CONDITION_BASED_CLAIM      => '請求締払い',
            self::CONNECTION_PAYMENT_CONDITION_OTHER            => 'その他',
        ),
        
		'connection_payment_condition_month' => array(
            self::CONNECTION_PAYMENT_CONDITION_THIS_MONTH       => '当月',
            self::CONNECTION_PAYMENT_CONDITION_NEXT_MONTH       => '翌月',
            self::CONNECTION_PAYMENT_CONDITION_2NEXT_MONTH      => '翌々月',
        ),

		// 支払条件
		'inv_fin_type' => array(
            self::INV_FIN_TYPE_INVEST_SOURCE                    => '融資元（借入元）',
            self::INV_FIN_TYPE_INVEST_DESTINATION               => '融資先（貸付先）',
            self::INV_FIN_TYPE_FINANCE_SOURCE                   => '投資元（出資元）',
            self::INV_FIN_TYPE_FINANCE_DESTINATION              => '投資先（出資先）',
        ),

		// 営業案件管理 発足区分
		'progress_start_type' => array(
            self::PROGRESS_START_TYPE_A                    => 'A 創造',
            self::PROGRESS_START_TYPE_B1                   => 'B1 クロス',
            self::PROGRESS_START_TYPE_B2                   => 'B2 受け新規',
            self::PROGRESS_START_TYPE_C                    => 'C 既存取引',
        ),

		'progress_start_type_short' => array(
            self::PROGRESS_START_TYPE_A                    => 'A',
            self::PROGRESS_START_TYPE_B1                   => 'B1',
            self::PROGRESS_START_TYPE_B2                   => 'B2',
            self::PROGRESS_START_TYPE_C                    => 'C',
        ),
        
		// 営業案件管理 重要度
		'progress_importance' => array(
            self::PROGRESS_IMPORTNCE_C1                    => 'C1 顧客・重要大',
            self::PROGRESS_IMPORTNCE_C2                    => 'C2 顧客・重要中',
            self::PROGRESS_IMPORTNCE_C3                    => 'C3 顧客・重要小',
            self::PROGRESS_IMPORTNCE_P1                    => 'P1 協業先・重要大',
            self::PROGRESS_IMPORTNCE_P2                    => 'P2 協業先・重要小',
            self::PROGRESS_IMPORTNCE_S1                    => 'S1 仕入先・重要大',
            self::PROGRESS_IMPORTNCE_S2                    => 'S2 仕入先・重要小',
            self::PROGRESS_IMPORTNCE_Z1                    => 'Z1 その他・重要大',
            self::PROGRESS_IMPORTNCE_Z2                    => 'Z2 その他・重要小',
        ),

		'progress_importance_short' => array(
            self::PROGRESS_IMPORTNCE_C1                    => 'C1',
            self::PROGRESS_IMPORTNCE_C2                    => 'C2',
            self::PROGRESS_IMPORTNCE_C3                    => 'C3',
            self::PROGRESS_IMPORTNCE_P1                    => 'P1',
            self::PROGRESS_IMPORTNCE_P2                    => 'P2',
            self::PROGRESS_IMPORTNCE_S1                    => 'S1',
            self::PROGRESS_IMPORTNCE_S2                    => 'S2',
            self::PROGRESS_IMPORTNCE_Z1                    => 'Z1',
            self::PROGRESS_IMPORTNCE_Z2                    => 'Z2',
        ),
        
		// 営業案件管理 可能性
		'progress_possibility' => array(
            self::PROGRESS_POSSIBILITY_HIGH                => '◎ 確度高い',
            self::PROGRESS_POSSIBILITY_HALF                => '○ 確度50%',
            self::PROGRESS_POSSIBILITY_LOW                 => '△ 確度低い',
            self::PROGRESS_POSSIBILITY_UNKNOWN             => '？ まだ不明',
            self::PROGRESS_POSSIBILITY_RECIEVED            => '★ 本番受注',
            self::PROGRESS_POSSIBILITY_SAMPLE_RECIEVED     => '☆ 試作受注',
            self::PROGRESS_POSSIBILITY_FAILURE             => '× 失注',
        ),

		'progress_possibility_short' => array(
            self::PROGRESS_POSSIBILITY_HIGH                => '◎',
            self::PROGRESS_POSSIBILITY_HALF                => '○',
            self::PROGRESS_POSSIBILITY_LOW                 => '△',
            self::PROGRESS_POSSIBILITY_UNKNOWN             => '？',
            self::PROGRESS_POSSIBILITY_RECIEVED            => '★',
            self::PROGRESS_POSSIBILITY_SAMPLE_RECIEVED     => '☆',
            self::PROGRESS_POSSIBILITY_FAILURE             => '×',
        ),

	
	    ///////////////////////////////////////////
	    // 承認
	    ///////////////////////////////////////////
	    'approval_status' => array(
            self::APPROVAL_STATUS_PENDDING           => '承認待ち',
            self::APPROVAL_STATUS_MOD_REQUEST        => '修正依頼済み',
            self::APPROVAL_STATUS_REJECTED           => '却下済み',
            self::APPROVAL_STATUS_APPROVED           => '承認済み',
        ),
		
		// 承認申請種別
	    'approval_type' => array(
            self::APPROVAL_TYPE_ESTIMATE             => '提出見積',
            self::APPROVAL_TYPE_INVOICE              => '請求書発行',
            self::APPROVAL_TYPE_SUPPLY_COMPETITION   => 'コンペ',
            self::APPROVAL_TYPE_COST                 => '原価計算',
            self::APPROVAL_TYPE_ORDERFORM            => '発注管理',
            self::APPROVAL_TYPE_ONLINE_PURCHASE      => 'ネット購入委託管理',
            
            self::APPROVAL_TYPE_PAYABLE              => '請求支払申請',
            self::APPROVAL_TYPE_PAYABLE_CARD         => 'カード支払申請',
            self::APPROVAL_TYPE_PAYABLE_MONTHLY      => '毎月支払管理 新規支払予定',
            self::APPROVAL_TYPE_PAYABLE_TEMPLATE     => '毎月支払管理',
            
            self::APPROVAL_TYPE_ORDER                => '受注管理',
            self::APPROVAL_TYPE_SAMPLE_WASTE         => 'サンプル出荷/破棄',
            
            self::APPROVAL_TYPE_RECEIVABLE           => 'その他入金予定申請',
            self::APPROVAL_TYPE_RECEIVABLE_CARD      => 'カード返金予定申請',
            self::APPROVAL_TYPE_RECEIVABLE_MONTHLY   => '毎月入金管理 新規入金予定',
            self::APPROVAL_TYPE_RECEIVABLE_TEMPLATE  => '毎月入金管理',
            
            self::APPROVAL_TYPE_INVENTORY            => '在庫棚卸',
        ),
        
	    ///////////////////////////////////////////
	    // 見積
	    ///////////////////////////////////////////
	    'estimate_status' => array(
            self::ESTIMATE_STATUS_DRAFT              => '作成中',
            self::ESTIMATE_STATUS_PENDING            => '承認待ち',
            self::ESTIMATE_STATUS_MOD_REQUEST        => '修正依頼',
            self::ESTIMATE_STATUS_REJECTED           => '却下',
            self::ESTIMATE_STATUS_APPROVED           => '承認済み',
            self::ESTIMATE_STATUS_SUBMITTED          => '提出完了',
        ),

	    'estimate_version_status' => array(
            self::ESTIMATE_VERSION_STATUS_CLOSED     => 'クローズ',
            self::ESTIMATE_VERSION_STATUS_MAKING     => '作成中',
            self::ESTIMATE_VERSION_STATUS_PENDING    => '承認待ち',
            self::ESTIMATE_VERSION_MOD_REQUEST       => '修正依頼',
            self::ESTIMATE_VERSION_STATUS_REJECTED   => '却下',
            self::ESTIMATE_VERSION_STATUS_APPROVED   => '承認済み',
            self::ESTIMATE_VERSION_STATUS_SUBMITTED  => '提出完了',
        ),

	    ///////////////////////////////////////////
	    // 調達管理
	    ///////////////////////////////////////////
    	// ステータス
	    'supply_status' => array(
		    self::SUPPLY_STATUS_CONSIDERING            => '検討中',
            self::SUPPLY_STATUS_USING                  => '調達中',
            self::SUPPLY_STATUS_NOT_USING              => '調達不採用',
            self::SUPPLY_STATUS_FINISHED_USING         => '調達終了',
        ),

    	// 仕入れ調達先ステータス
	    'supplier_status' => array(
		    self::SUPPLIER_STATUS_CONSIDERING          => '検討中',
            self::SUPPLIER_STATUS_USING                => '採用中',
            self::SUPPLIER_STATUS_NOT_USING            => '不採用',
            self::SUPPLIER_STATUS_FINISHED_USING       => '採用終了',
        ),
        
        // 原料製品 非売用途
	    'uses_not_sales' => array(
            self::USE_NOTSALE_COMPONENT                => '製造用構成品',
            self::USE_NOTSALE_ACCESS_INFO              => '情報入手のみ',
        ),

    	// コンペステータス
	    'competition_status' => array(
            self::COMPETITION_STATUS_PROGRESS          => '進行中',
            self::COMPETITION_STATUS_APPROVAL_PENDDING => '完了・承認申請中',
            self::COMPETITION_STATUS_APPROVED          => '完了・承認申請済み',
            self::COMPETITION_STATUS_STOPPED           => '途中終了・一時凍結',
        ),
        
    	// レーティング
	    'competition_rating' => array(
            self::COMPETITION_RATING_A_PLUS_PLUS       => 'A★★',
            self::COMPETITION_RATING_A_PLUS            => 'A★',
            self::COMPETITION_RATING_A                 => 'A',
            self::COMPETITION_RATING_B_PLUS            => 'B★',
            self::COMPETITION_RATING_B                 => 'B',
            self::COMPETITION_RATING_C                 => 'C',
        ),

	    ///////////////////////////////////////////
	    // 原価計算
	    ///////////////////////////////////////////
	    'cost_calc_status' => array(
            self::COST_CALC_STATUS_NOT_CREATED         => '原価計算未作成',
            self::COST_CALC_STATUS_EDITING             => '編集中',
            self::COST_CALC_STATUS_APPROVAL_PENDDING   => '承認申請中',
            self::COST_CALC_STATUS_APPROVED            => '承認済み',
        ),
    
	    'export_type' => array(
            self::EXPORT_TYPE_INTERNATIONAL_MAIL       => '国際郵便・宅急便（EMS／クーリエ）',
            self::EXPORT_TYPE_AIR_TRANSPORT            => '輸出 航空輸送',
            self::EXPORT_TYPE_MARINE_TRANSPORT         => '輸出 海上輸送',
            self::EXPORT_TYPE_OTHER                    => 'その他',
        ),

	    'cost_processing_type' => array(
            self::PROCESSING_OWN                       => '自社作業',
            self::PROCESSING_SUBCONTRACTING            => '外部委託工程',
        ),
        
	    ///////////////////////////////////////////
	    // 受注
	    ///////////////////////////////////////////
	    'shipment_timing' => array(
            self::SHIPMENT_TIMING_SOON                     => '承認と同時に出荷指示',
            self::SHIPMENT_TIMING_AFTER_PAYMENT            => '入金確認後',
            self::SHIPMENT_TIMING_DATE_SPECIFICATION       => '到着日を指定',
            self::SHIPMENT_TIMING_PENDING                  => '未定',
            self::SHIPMENT_TIMING_NONE                     => '発送なし',
        ), 
	    
	    'direct_order_status' => array(
	    	self::DIRECT_ORDER_STATUS_DRAFT                => '下書き',
	    	self::DIRECT_ORDER_STATUS_PENDING              => '承認待ち',
	    	self::DIRECT_ORDER_STATUS_MOD_REQUEST          => '修正依頼',
	    	self::DIRECT_ORDER_STATUS_APPROVED             => '承認済み',
	    	self::DIRECT_ORDER_STATUS_INVOICE_COMPLETED    => '請求書発行完了',
	    	self::DIRECT_ORDER_STATUS_CANCELED             => 'キャンセル',
        ),

	    'direct_order_status_search' => array(
	    	self::DIRECT_ORDER_STATUS_DRAFT                => '下書き',
	    	self::DIRECT_ORDER_STATUS_PENDING              => '承認待ち',
	    	self::DIRECT_ORDER_STATUS_MOD_REQUEST          => '修正依頼',
	    	self::DIRECT_ORDER_STATUS_APPROVED             => '承認済み',
	    	self::DIRECT_ORDER_STATUS_INVOICE_COMPLETED    => '請求書発行完了',
	    	self::DIRECT_ORDER_STATUS_NOT_COMPLETED        => '請求書未発行',
	    	self::DIRECT_ORDER_STATUS_CANCELED             => 'キャンセル',
        ),
        
	    'direct_order_shipment_status' => array(
            self::DIRECT_ORDER_STATUS_NO_SHIPPING          => '出荷なし',
            self::DIRECT_ORDER_STATUS_WAIT_FOR_PAYMENT     => '入金待ち',
            self::DIRECT_ORDER_STATUS_SHIPMENT_DIRECTED    => '出荷指示済み',
            self::DIRECT_ORDER_STATUS_SHIPPED              => '納品済',
            self::DIRECT_ORDER_STATUS_SHIPMENT_PENDING     => '出荷保留',
            self::DIRECT_ORDER_STATUS_NOT_SHIPPED          => '未出荷',
        ),

		'delivery_agent' => array( 
			self::DELIVERY_AGENT_YAMATO                    => 'ヤマト運輸',
			self::DELIVERY_AGENT_SAGAWA                    => '佐川急便',
			self::DELIVERY_AGENT_JP                        => '日本郵便',
			self::DELIVERY_AGENT_NITTSU                    => '日本通運',
			self::DELIVERY_AGENT_SEINO                     => '西濃運輸',
			
			self::DELIVERY_AGENT_EMS                       => 'EMS（国際スピード郵便）',
			self::DELIVERY_AGENT_DHL                       => 'DHL',
			self::DELIVERY_AGENT_FEDEX                     => 'FedEX',
			self::DELIVERY_AGENT_UPS                       => 'UPS',
			self::DELIVERY_AGENT_TNT                       => 'TNT',
			self::DELIVERY_AGENT_OCS                       => 'OCS',
			self::DELIVERY_AGENT_OTHER                     => 'その他',
        ),
        
        'delivery_cost' => array(
            self::DELIVERY_COST_US                         => '当社負担',
            self::DELIVERY_COST_ORDERER                    => '発注者負担',
            self::DELIVERY_COST_SHARE                      => '一部発注者負担',
        ),    

	    ///////////////////////////////////////////
	    // 請求書
	    ///////////////////////////////////////////
        'invoice_status' => array(
            self::INVOICE_STATUS_DRAFT                     => '下書き',
            self::INVOICE_STATUS_PENDING                   => '承認待ち',
            self::INVOICE_STATUS_MOD_REQUEST               => '修正依頼',
            self::INVOICE_STATUS_APPROVED                  => '承認済み',
            self::INVOICE_STATUS_SUBMITTED                 => '提出済み',
            self::INVOICE_STATUS_PAYABLED_ADDED            => '入金予定登録済み', 
        ),
        
        'invoice_status_search' => array(
            self::INVOICE_STATUS_DRAFT                     => '下書き',
            self::INVOICE_STATUS_PENDING                   => '承認待ち',
            self::INVOICE_STATUS_MOD_REQUEST               => '修正依頼',
            self::INVOICE_STATUS_APPROVED                  => '承認済み',
            self::INVOICE_STATUS_SUBMITTED                 => '提出済み',
            self::INVOICE_STATUS_PAYABLED_ADDED            => '入金予定登録済み',
            self::INVOICE_STATUS_PAYABLED_NOT_ADDED        => '入金予定未登録',
            self::INVOICE_STATUS_CANCELED                  => 'キャンセル',
        ),
		
		// 請求書タイプ
        'invoice_type' => array(
		    self::INVOICE_TYPE_CREATE                      => '請求書作成',
		    self::INVOICE_TYPE_UPLOAD                      => '請求書アップロード',
        ),
        
	    ///////////////////////////////////////////
	    // 発注書
	    ///////////////////////////////////////////
        'order_form_status' => array(
            self::ORDER_FORM_STATUS_DRAFT                     => '下書き',
            self::ORDER_FORM_STATUS_PENDING                   => '承認待ち',
            self::ORDER_FORM_STATUS_MOD_REQUEST               => '修正依頼',
            self::ORDER_FORM_STATUS_APPROVED                  => '承認済み',
            self::ORDER_FORM_STATUS_SUBMITTED                 => '発注完了',
        ),

        'order_form_status_search' => array(
            self::ORDER_FORM_STATUS_DRAFT                     => '下書き',
            self::ORDER_FORM_STATUS_PENDING                   => '承認待ち',
            self::ORDER_FORM_STATUS_MOD_REQUEST               => '修正依頼',
            self::ORDER_FORM_STATUS_APPROVED                  => '承認済み',
            self::ORDER_FORM_STATUS_SUBMITTED                 => '発注完了',
            self::ORDER_FORM_STATUS_NOT_SUBMITTED             => '発注未完了',
            self::ORDER_FORM_STATUS_CANCELED                  => 'キャンセル',
        ),
		
		// 発注 納品受領ステータス
        'order_form_deliveried_status' => array(
            self::ORDER_FORM_DELIVERIED_STATUS_NONE           => '未受領',
            self::ORDER_FORM_DELIVERIED_STATUS_RECIEVED       => '受領済み',
        ),

        'order_form_type' => array(
            self::ORDER_FORM_TYPE_CREATE                      => '注文書作成',
            self::ORDER_FORM_TYPE_UPLOAD                      => '注文書アップロード',
        ),

        'order_form_payable_status' => array(
            self::ORDER_FORM_PAYABLE_BACKLOG                  => '申請未完了',
            self::ORDER_FORM_PAYABLE_COMPLETED                => '申請完了',
        ),
        
	    ///////////////////////////////////////////
	    // ネット購入委託
	    ///////////////////////////////////////////
        'online_purchase_status' => array(
            self::ONLINE_PURCHASE_STATUS_DRAFT                => '下書き',
            self::ONLINE_PURCHASE_STATUS_PENDING              => '承認待ち',
            self::ONLINE_PURCHASE_STATUS_MOD_REQUEST          => '修正依頼',
            self::ONLINE_PURCHASE_STATUS_APPROVED             => '承認済み',
            self::ONLINE_PURCHASE_STATUS_CANCEL               => 'キャンセル',
        ),
        
        'online_purchase_status_search' => array(
            self::ONLINE_PURCHASE_STATUS_DRAFT                => '下書き',
            self::ONLINE_PURCHASE_STATUS_PENDING              => '承認待ち',
            self::ONLINE_PURCHASE_STATUS_MOD_REQUEST          => '修正依頼',
            self::ONLINE_PURCHASE_STATUS_APPROVED             => '承認済み',
            self::ONLINE_PURCHASE_STATUS_NOT_APPROVED         => '未承認',
            self::ONLINE_PURCHASE_STATUS_CANCEL               => 'キャンセル',
        ),

    
	    ///////////////////////////////////////////
	    // 内税
	    ///////////////////////////////////////////
        'including_tax' => array(
            self::INCLUDING_TAX_NO                            => '税別価格',
            self::INCLUDING_TAX_YES                           => '税込価格',
        ),  

	    ///////////////////////////////////////////
	    // 金融機関・クレジットカード
	    ///////////////////////////////////////////
        // 振込先銀行口座登録種別
        'bank_registered_type' => array(
            self::BANK_REGISTERED_TYPE_FASS                  => 'FASS上',
            self::BANK_REGISTERED_TYPE_GOOSA_SP              => 'goosa SP',
            self::BANK_REGISTERED_TYPE_GOOSA_BY              => 'goosa BY',
            self::BANK_REGISTERED_TYPE_GOOSCA_SP             => 'GOOSCA 店舗',
            self::BANK_REGISTERED_TYPE_GOOSCA_BY             => 'GOOSCA 会員',
	    ),

		// 基本銀行選択肢
		'basic_bank' => array(
			self::BASIC_BANK_JAPAN_NET                       => 'ジャパンネット銀行',
			self::BASIC_BANK_RAKUTEN                         => '楽天銀行',
			self::BASIC_BANK_MUFG                            => '三菱UFJ銀行',
			self::BASIC_BANK_SMBC                            => '三井住友銀行',
			self::BASIC_BANK_RISONA                          => 'りそな銀行',
			self::BASIC_BANK_MIZUHO                          => 'みずほ銀行',
			self::BASIC_BANK_YUUTYO                          => 'ゆうちょ銀行',
			self::BASIC_BANK_OTHER                           => 'その他',
		),

		// 振込先口座確認ステータス
		'bank_confirm_status' => array(
			self::BANK_CONFIRM_STATUS_NONE                   => '未登録',
			self::BANK_CONFIRM_STATUS_RENEWALED              => '未確認',
			self::BANK_CONFIRM_STATUS_CONFIRMED              => '確認済み',
		),
		
		// 基本銀行金融機関コード
		'basic_bank_code' => array(
			self::BASIC_BANK_JAPAN_NET                       => '0033',
			self::BASIC_BANK_RAKUTEN                         => '0036',
			self::BASIC_BANK_MUFG                            => '0005',
			self::BASIC_BANK_SMBC                            => '0009',
			self::BASIC_BANK_RISONA                          => '0010',
			self::BASIC_BANK_MIZUHO                          => '0001',
			self::BASIC_BANK_YUUTYO                          => '9900',
			self::BASIC_BANK_OTHER                           => '',
		),

		// 預金種別
		/*
		'bank_account_type' => array(
            self::BANK_ACCOUNT_TYPE_NORMAL                      => '納入締払い',
            self::BANK_ACCOUNT_TYPE_CHECKING                    => '請求締払い',
        ),
        */

	    // 銀行口座種別
	    'bank_account_type' => array(
            self::BANK_ACCOUNT_TYPE_GENERAL                 => '普通預金',
            self::BANK_ACCOUNT_TYPE_CURRENT                 => '当座預金',
            self::BANK_ACCOUNT_TYPE_TOTAL                   => '総合口座',
            self::BANK_ACCOUNT_TYPE_FIX_DEPOSIT             => '定期預金',
            self::BANK_ACCOUNT_TYPE_SAVING_DEPOSIT          => '貯蓄預金', 
			self::BANK_ACCOUNT_TYPE_LARGE_DEPOSIT           => '大口定期預金',
			self::BANK_ACCOUNT_TYPE_CUMULATIVE_DEPOST       => '積立定期預金3', 
        ),

        // 銀行口座種別
        'shop_fee_bank_account_type' => array(
			self::BANK_ACCOUNT_TYPE_GENERAL                  => '普通預金',
            self::BANK_ACCOUNT_TYPE_CURRENT                  => '当座預金',
		),	

		'bank_import_format' => array(
			self::BANK_IMPORT_FORMAT_NORMAL                 => '一般',
			self::BANK_IMPORT_FORMAT_JNB                    => 'ジャパンネット銀行',
		),
		
	    ///////////////////////////////////////////
	    // 会計・売掛・買掛
	    ///////////////////////////////////////////
    	// 売掛登録種別
	    'account_totaling_row_type' => array(
	    	self::ACCOUNT_TOTALING_ROW_TYPE_HEADING         => '見出し',
            self::ACCOUNT_TOTALING_ROW_TYPE_TOTAL           => '合計',
            self::ACCOUNT_TOTALING_ROW_TYPE_FREE            => '自由入力',
            self::ACCOUNT_TOTALING_ROW_TYPE_REFERENCE       => '引用',
        ),
        
		// 売掛登録種別
	    'receivable_type' => array(
	    	self::RECEIVABLE_TYPE_INVOICE                   => '請求書発行分',
	    	self::RECEIVABLE_TYPE_SITE_DATA                 => 'サイト連動',
            self::RECEIVABLE_TYPE_MONTHLY                   => '毎月入金項目',
            self::RECEIVABLE_TYPE_OTHER                     => 'その他入金項目',
            self::RECEIVABLE_TYPE_HISTORY                   => '明細から追加',
        ),

		// 売掛申請ステータス
	    'receivable_status' => array(
	    	self::RECEIVABLE_STATUS_DRAFT                   => '下書き',
            self::RECEIVABLE_STATUS_PENDING                 => '承認待ち',
            self::RECEIVABLE_STATUS_MOD_REQUEST             => '修正依頼',
            self::RECEIVABLE_STATUS_APPROVED                => '承認済み',
            self::RECEIVABLE_STATUS_ADDED_FROM_HISTORY      => '明細から追加',
        ),
	    
		// 売掛申請入金ステータス
	    'receivable_payment_status' => array(
            self::RECEIVABLE_PAYMENT_STATUS_UNRECEIVED      => '未入金',
            self::RECEIVABLE_PAYMENT_STATUS_RECEIVED        => '入金済',
            self::RECEIVABLE_PAYMENT_STATUS_CANCELED        => 'キャンセル',
        ),

 		// 毎月入金管理テンプレート種別
	    'receivable_template_type' => array(
	    	self::RECEIVABLE_TEMPLATE_TYPE_FIXED            => '固定入金額',
            self::RECEIVABLE_TEMPLATE_TYPE_VARIABLE         => '毎月変動',
        ),

 		// 毎月入金管理承認ステータス
	    'receivable_template_status' => array(
	    	self::RECEIVABLE_TEMPLATE_STATUS_DRAFT          => '下書き',
            self::RECEIVABLE_TEMPLATE_STATUS_PENDING        => '承認待ち',
            self::RECEIVABLE_TEMPLATE_STATUS_MOD_REQUEST    => '修正依頼',
            self::RECEIVABLE_TEMPLATE_STATUS_APPROVED       => '承認済み',
            self::RECEIVABLE_TEMPLATE_STATUS_FINISHED       => '毎月入金終了',
        ),
		
		// 毎月入金管理承認ステータス(検索)
	    'receivable_template_status_search' => array(
	    	self::RECEIVABLE_TEMPLATE_STATUS_DRAFT          => '下書き',
            self::RECEIVABLE_TEMPLATE_STATUS_PENDING        => '承認待ち',
            self::RECEIVABLE_TEMPLATE_STATUS_MOD_REQUEST    => '修正依頼',
            self::RECEIVABLE_TEMPLATE_STATUS_APPROVED       => '承認済み',
            self::RECEIVABLE_TEMPLATE_STATUS_FINISHED       => '毎月入金終了',
            self::RECEIVABLE_TEMPLATE_STATUS_NOT_APPROVED   => '未承認',
        ),
        
    	// 買掛支払種別
	    'payable_paying_type' => array(
            self::PAYABLE_PAYING_TYPE_INVOICE               => '請求支払申請',
            self::PAYABLE_PAYING_TYPE_SITE_DATA             => 'サイト連動支払',
            self::PAYABLE_PAYING_TYPE_CREDIT_CARD           => 'カード支払申請',
            self::PAYABLE_PAYING_TYPE_MONTHLY               => '毎月支払管理',
        ),

	    // 買掛支払方法(全て・支払完了確認用)
	    'payable_paying_method_list' => array(
            self::PAYABLE_PAYING_METHOD_BANK                => '銀行振込',
            self::PAYABLE_PAYING_METHOD_PAYMENT_FORM        => 'コンビニ・ゆうちょ後払い',
            self::PAYABLE_PAYING_METHOD_AUTO                => '口座振替',
            self::PAYABLE_PAYING_METHOD_CASH                => '小口現金',
            self::PAYABLE_PAYING_METHOD_CREDIT              => 'カード払い',
            self::PAYABLE_PAYING_METHOD_OTHER               => 'その他',
        ),
        
	    'payable_paying_method_all' => array(
            self::PAYABLE_PAYING_METHOD_BANK                => '銀行振込',
            self::PAYABLE_PAYING_METHOD_PAYMENT_FORM        => 'コンビニ・ゆうちょ後払い',
            self::PAYABLE_PAYING_METHOD_AUTO                => '口座振替',
            self::PAYABLE_PAYING_METHOD_CASH                => '小口現金',
            self::PAYABLE_PAYING_METHOD_CREDIT              => 'カード払い',
            self::PAYABLE_PAYING_METHOD_OTHER               => 'その他',
        ),
        
	    // 買掛支払方法(請求支払)
	    'payable_paying_method_invoice' => array(
            self::PAYABLE_PAYING_METHOD_BANK                => '銀行振込',
            self::PAYABLE_PAYING_METHOD_PAYMENT_FORM        => 'コンビニ・ゆうちょ後払い',
            self::PAYABLE_PAYING_METHOD_CASH                => '小口現金',
            self::PAYABLE_PAYING_METHOD_OTHER               => 'その他',
        ),

	    // 買掛支払方法(銀行割当)
	    'payable_paying_method_bank' => array(
            self::PAYABLE_PAYING_METHOD_BANK                => '銀行振込',
            self::PAYABLE_PAYING_METHOD_AUTO                => '口座振替',
            self::PAYABLE_PAYING_METHOD_CASH                => '小口現金',
        ),

	    // 買掛支払方法(ネット購入委託管理)
	    'payable_paying_method_purchase' => array(
            self::PAYABLE_PAYING_METHOD_BANK                => '銀行振込',
            self::PAYABLE_PAYING_METHOD_PAYMENT_FORM        => 'コンビニ・ゆうちょ後払い',
            self::PAYABLE_PAYING_METHOD_CREDIT              => 'カード払い',
            self::PAYABLE_PAYING_METHOD_OTHER               => 'その他',
        ),
        
	    // 買掛支払方法(毎月支払)
	    'payable_paying_method_template' => array(
            self::PAYABLE_PAYING_METHOD_BANK                => '銀行振込',
            self::PAYABLE_PAYING_METHOD_AUTO                => '口座振替',
            self::PAYABLE_PAYING_METHOD_PAYMENT_FORM        => 'コンビニ・ゆうちょ後払い',
            self::PAYABLE_PAYING_METHOD_CREDIT              => 'カード払い',
            self::PAYABLE_PAYING_METHOD_OTHER               => 'その他',
        ),

		// 支払申請ステータス
	    'payable_status' => array(
	    	self::PAYABLE_STATUS_DRAFT                      => '下書き',
            self::PAYABLE_STATUS_PENDING                    => '承認待ち',
            self::PAYABLE_STATUS_MOD_REQUEST                => '修正依頼',
            self::PAYABLE_STATUS_APPROVED                   => '承認済み',
            self::PAYABLE_STATUS_ADDED_FROM_HISTORY         => '明細から追加',
        ),

		// 毎月支払管理ステータス
	    'payable_template_status' => array(
	    	self::PAYABLE_TEMPLATE_STATUS_DRAFT             => '下書き',
            self::PAYABLE_TEMPLATE_STATUS_PENDING           => '承認待ち',
            self::PAYABLE_TEMPLATE_STATUS_MOD_REQUEST       => '修正依頼',
            self::PAYABLE_TEMPLATE_STATUS_APPROVED          => '承認済み',
            self::PAYABLE_TEMPLATE_STATUS_FINISHED          => '毎月支払終了',
        ),

		// 毎月支払管理ステータス(検索)
	    'payable_template_status_search' => array(
	    	self::PAYABLE_TEMPLATE_STATUS_DRAFT             => '下書き',
            self::PAYABLE_TEMPLATE_STATUS_PENDING           => '承認待ち',
            self::PAYABLE_TEMPLATE_STATUS_MOD_REQUEST       => '修正依頼',
            self::PAYABLE_TEMPLATE_STATUS_APPROVED          => '承認済み',
            self::PAYABLE_TEMPLATE_STATUS_FINISHED          => '毎月支払終了',
            self::PAYABLE_TEMPLATE_STATUS_NOT_APPROVED      => '未承認',
        ),
        
		// 買掛支払ステータス
	    'payable_payment_status' => array(
	    	self::PAYABLE_PAYMENT_STATUS_UNPAID               => '未払',
	    	self::PAYABLE_PAYMENT_STATUS_PENDDING             => '保留',
	    	
	        self::PAYABLE_PAYMENT_STATUS_PLANNED_NOT_APPROVED => '予約承認待ち',
	    	self::PAYABLE_PAYMENT_STATUS_PLANNED_EXPIRED      => '承認期限切れ',
	    	
	    	self::PAYABLE_PAYMENT_STATUS_PLANNED              => '支払予約済',
            self::PAYABLE_PAYMENT_STATUS_PAID                 => '支払済',
            
            self::PAYABLE_PAYMENT_STATUS_OFFSET_AND_CLOSE     => '相殺完結',
            self::PAYABLE_PAYMENT_STATUS_OFFSET_AND_PAY       => '相殺後振込',
            self::PAYABLE_PAYMENT_STATUS_CANCELED             => 'キャンセル',
        ),
        
        // 買掛支払ステータス(検索用)
	    'payable_payment_status_search' => array(
	    	self::PAYABLE_PAYMENT_STATUS_UNPAID               => '未払',
	    	self::PAYABLE_PAYMENT_STATUS_PENDDING             => '保留',
	    	self::PAYABLE_PAYMENT_STATUS_UNPAID_PENDDING      => '未払＆保留',
	    	
	    	self::PAYABLE_PAYMENT_STATUS_PLANNED_NOT_APPROVED => '予約承認待ち',
	    	self::PAYABLE_PAYMENT_STATUS_PLANNED_EXPIRED      => '承認期限切れ',
	    	
	    	self::PAYABLE_PAYMENT_STATUS_PLANNED              => '支払予約済',
            self::PAYABLE_PAYMENT_STATUS_PAID                 => '支払済',
            
            self::PAYABLE_PAYMENT_STATUS_OFFSET_AND_CLOSE     => '相殺完結',
            self::PAYABLE_PAYMENT_STATUS_OFFSET_AND_PAY       => '相殺後振込', 
            
            self::PAYABLE_PAYMENT_STATUS_CANCELED             => 'キャンセル',
        ),

		// 買掛テンプレート種別
	    'payable_template_type' => array(
	    	self::PAYABLE_TEMPLATE_TYPE_FIXED               => '固定費用',
            self::PAYABLE_TEMPLATE_TYPE_VARIABLE            => '毎月変動',
        ),
	
        // 税区分(課税・非課税)
	    'tax_division' => array(
	    	self::TAX_DIVISION_TAXATION                     => '課税',
            self::TAX_DIVISION_EXEMPTION                    => '非課税',
        ),
        
        // 発注支払申請実施ステータス
	    'order_form_payable_status' => array(
	    	self::ORDER_FORM_PAYABLE_STATUS_BACKLOG         => '申請未完了',
            self::ORDER_FORM_PAYABLE_STATUS_FINISHED        => '申請完了',
        ),
        
		// 銀行口座履歴 割当ステータス
	    'bank_history_item_status' => array(
            self::BANK_HISTORY_ITEM_STATUS_NONE              => '割当未完了',
            self::BANK_HISTORY_ITEM_STATUS_ATTACHED          => '割当完了',
        ),
        
		// クレジットカード請求履歴 割当ステータス
	    'card_history_item_status' => array(
            self::CARD_HISTORY_ITEM_STATUS_NONE              => '割当未完了',
            self::CARD_HISTORY_ITEM_STATUS_ATTACHED          => '割当完了',
        ),
    
	    ///////////////////////////////////////////
	    // 設定
	    ///////////////////////////////////////////
		// 明細書テンプレートタイプ 設定保存用カラム名
	    'template_type_key' => array(
            self::STATEMENT_TEMPLATE_TYPE_DEFAULT_1          => 'statement_tamplate_1',
            self::STATEMENT_TEMPLATE_TYPE_DEFAULT_2          => 'statement_tamplate_2',
            self::STATEMENT_TEMPLATE_TYPE_DEFAULT_3          => 'statement_tamplate_3',
            self::STATEMENT_TEMPLATE_TYPE_SUBSCRIPTION_1     => 'statement_tamplate_subscription_1',
            self::STATEMENT_TEMPLATE_TYPE_SUBSCRIPTION_2     => 'statement_tamplate_subscription_2', 
			self::STATEMENT_TEMPLATE_TYPE_SUBSCRIPTION_3     => 'statement_tamplate_subscription_3', 
        ),
        
        // 明細書テンプレートタイプ 表示名称
	    'template_type_name' => array(
            self::STATEMENT_TEMPLATE_TYPE_DEFAULT_1          => '通常テンプレート1',
            self::STATEMENT_TEMPLATE_TYPE_DEFAULT_2          => '通常テンプレート2',
            self::STATEMENT_TEMPLATE_TYPE_DEFAULT_3          => '通常テンプレート3',
            self::STATEMENT_TEMPLATE_TYPE_SUBSCRIPTION_1     => '定期テンプレート1',
            self::STATEMENT_TEMPLATE_TYPE_SUBSCRIPTION_2     => '定期テンプレート2', 
			self::STATEMENT_TEMPLATE_TYPE_SUBSCRIPTION_3     => '定期テンプレート3', 
        ),

	    ///////////////////////////////////////////
	    // マニュアル
	    ///////////////////////////////////////////
	    // マニュアル機密度
	    'manual_confidentiality' => array(
            self::MANUAL_CONDIDENTIALITY_MOST                => 'A 最重要機密',
            self::MANUAL_CONDIDENTIALITY_IMPORTANT           => 'B 重要機密',
            self::MANUAL_CONDIDENTIALITY_SPECIFIC            => 'C 特定機密',
            self::MANUAL_CONDIDENTIALITY_GENERAL             => 'D 一般社内機密',
            self::MANUAL_CONDIDENTIALITY_ASSOCIATED          => 'E 関連社外秘', 
        ),
        
	    // コンテンツタイプ
	    'manual_content_type' => array(
            self::MANUAL_CONTENT_TYPE_TEXT                   => 'テキスト',
            self::MANUAL_CONTENT_TYPE_IMAGE                  => '画像',
            self::MANUAL_CONTENT_TYPE_FILE                   => 'ファイル',
        ),


    );
    
    
    /**
     * 時
     * @param none
     * @return array
     */
    public static function getHourList()
    {
        $list = array();
        for($i = 0; $i < 24; $i++) {
            $hour = sprintf("%02d", $i);
            $list[$hour] = $hour;
        }
        return $list;
    }

    /**
     * 分
     * @param none
     * @return array
     */
    public static function getMinuteList()
    {
        $list = array();
        for($i = 0; $i < 60; $i++) {
            $hour = sprintf("%02d", $i);
            $list[$hour] = $hour;
        }
        return $list;
    }


    /**
     * 入金条件選択肢
     * @param none
     * @return array
     */
    public static function getPayementMonthlyList()
    {
        $list = array();
        for($i = 1; $i < 30; $i++) {
            $list[$i] = $i . '日';
        }
        $list[99] = '末';
        return $list;
    }
    
    /**
     * リリース後から今月の月選択リスト
     * @param  none
     * @return array
     */
    public static function getYearListFromRelease()
    { 
    	$selectList = array();

    	$zDate = new Zend_Date(NULL, NULL, 'ja_JP');
    	$zDate->add('1', Zend_Date::YEAR);
    	
    	$fromZDate = new Zend_Date('2018-01-01 00:00:00', NULL, 'ja_JP');
    	while (true) {
    		$selectList[$zDate->get('yyyy')] = $zDate->get('yyyy年');
    		
    		$zDate->sub('1', Zend_Date::MONTH);
    		if ($zDate->isEarlier($fromZDate) || $zDate->equals($fromZDate)) {
    			break;
    		}
    	}
    	
    	return $selectList;
    }

    /**
     * リリース後から今月の月選択リスト
     * @param  none
     * @return array
     */
    public static function getMonthListFromRelease()
    { 
    	$selectList = array();

    	$zDate = new Zend_Date(NULL, NULL, 'ja_JP');
    	//$zDate->add('1', Zend_Date::MONTH);
    	
    	$fromZDate = new Zend_Date('2018-05-31 23:59:59', NULL, 'ja_JP');
    	while (true) {
    		$selectList[$zDate->get('yyyy-MM')] = $zDate->get('yyyy年MM月');
    		
    		$zDate->sub('1', Zend_Date::MONTH);
    		if ($zDate->isEarlier($fromZDate) || $zDate->equals($fromZDate)) {
    			break;
    		}
    	}
    	
    	return $selectList;
    }
     
    /**
     * 月選択リスト
     * @param  none
     * @return array
     */
    public static function getMonthList()
    { 
    	$selectList = array();
    	
    	for ($count = 1; $count <= 12; $count++) {
    		$selectList[(string)$count] = $count . '月';
    	}
    	
    	return $selectList;
    }


    /**
     * 入金条件選択肢
     * @param none
     * @return array
     */
    public static function getIdAlpahabet()
    {
        return array('0', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
    } 

}



