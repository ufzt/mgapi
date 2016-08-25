<?php

// service 配置
return array(

  # 关注|取消关注 创客 & 一键关注 & 推荐创客
    'follow_maker'           =>   'FollowMakerBehavior',
    'unfollow_maker'         =>   'FollowMakerBehavior',
    'follow_list_maker'      =>   'FollowListMakerBehavior',
    'recommend_maker_list'   =>   'RecommendMakersBehavior',

  # 点赞 & 分享 & 发表话题 & 发表评论
    'click_liked'            =>   'ClickLikedBehavior',
    'click_share'            =>   'ClickShareBehavior',
    'write_topic'            =>   'WriteTopicBehavior',
    'write_comment'          =>   'WriteCommentBehavior',

  //发现 & 视频详情 & 话题列表 & 评论列表
    'discovery'              =>   'DiscoveryBehavior',
    'video_detail'           =>   'PresentVideoBehavior',
      'video_preview'        =>   'PreviewVideoBehavior',  #(用户)预览草稿
      'video_audit'          =>   'PreviewVideoBehavior',  #(管理员)审核视频
    'topic_list'             =>   'PresentTopicListBehavior',
    'comment_list'           =>   'PresentCommentListBehavior',

  # 我的个人中心
    'my_center'              =>   'ListMyCenterBehavior',
    'my_video_list'          =>   'ListMyVideoBehavior',
    'my_project_list'        =>   'ListMyProjectBehavior',

  # TA的个人中心
    'maker_center'           =>   'ListMakerCenterBehavior',
    'maker_video_list'       =>   'ListMakerVideoBehavior',

  # 发表 ugc 视频
    'search_ugc_tags'        =>   'UGCSearchTagsBehavior',
    'submit_ugc_video'       =>   'UGCCUVideoBehavior',
    'list_draft_ugc_videos'  =>   'UGCListVideosBehavior',
    'delete_draft_ugc_video' =>   'UGCRDVideoBehavior',
    'fetch_draft_ugc_video'  =>   'UGCRDVideoBehavior',
    'search_video_project'   =>   'SearchFactsBehavior',
    'hot_tags'               =>   'ListHotTagsBehavior',
    'hot_videos'             =>   'ListHotVideosBehavior',
    'hot_projects'           =>   'ListHotProjectsBehavior',
    'tag_detail'             =>   'PresentTagBehavior',
    'top_maker_list'         =>   'TopMakersBehavior',
    'check_version'          =>   'CheckAppVersionBehavior',
    'liked_man'              =>   'LikedManBehavior',

  # COS 接口
    'cos_sign'               =>   'COSPlay',
    'cos_upload'             =>   'COSUpload',

  # 消息 接口
    'my_message'             =>   'DigestNotifyBehavior',
    'notify_list'            =>   'ListNotifyBehavior',
    'like_message_list'      =>   'LikeDigestListBehavior',
    'follow_message_list'    =>   'FollowDigestListBehavior',
    'comment_message_list'   =>   'CommentDigestListBehavior',

  # 商品 接口
    'collect_goods'          =>   'GoodsCollectDoUndoBehavior',
    'uncollect_goods'        =>   'GoodsCollectDoUndoBehavior',
    'goods_list'             =>   'GoodsListBehavior',
    'goods_shop'             =>   'GoodsShopBehavior',
    'goods_collect_list'     =>   'GoodsCollectListBehavior',

  ### 分组 ###
  'activity' => include "service.x.activity.php",
);
