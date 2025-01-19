## MontageGTD 简介 


不止于GTD的WEB应用，除了支持番茄工作法、任务待办，更是支持笔记、RSS阅读、思维导图于一身的知识管理系统

> 取名 “蒙太奇”，这是个稍显拗口的电影术语，指通过对众多镜头进行不同方式的剪辑，从而展现出各异的立意 。即便在疫情期间，不少人断言电影将会走向消亡，但实际上电影或许是最具生命力、最难消亡的产业之一。人类在现实中往往显得渺小脆弱，而电影为我们提供了一个能够主宰想象空间的契机。
> 那么这个平台呢？它期望记录下我们这些小人物在平凡日常与非凡时刻的点点滴滴，如同经历一场 “浮生一日”。在这里，你既是导演，掌控着情节走向；也是编剧，书写着故事内容；更是演员，演绎着自己的人生。衷心希望大家所记录的每一件事、每一段成长历程，最终能够如同精心剪辑的蒙太奇镜头一般，共同拼凑出一部专属于我们生活的宏大电影。

![avatar](public/img/index.jpg)

## 快速体验
[https://task.congcong.us](https://task.congcong.us)

可以体验项目完整功能

## 开源地址
https://gitee.com/accacc/task

## 技术栈
基于php nginx mysql composer等工具
- php: >=7.0.0
- laravel: 5.5.*
- mysql:>=5.5.*

## 功能特性

1. 番茄工作法+任务列表
- [支持] 支持引导使用功能
- [支持] 支持番茄工作法时钟自定义，找到自己最高效的时间（最小需大于10分钟）
- [支持] 完成番茄钟之后，双击待办列表即可添加该番茄钟描述
- [支持] 针对未开番茄钟完成的有意义事情进行记录
- [支持] 待办事项支持提醒功能 支持deadline之后醒目提醒
- [支持] 待办事项支持四象限来管理，即不重要不紧急、重要不紧急、紧急不重要、不紧急不重要
- [支持] 待办事项支持分目标管理任务，方便后续归纳及总结
- [支持] 待办事项即将支持暂时隐藏长期任务一段时间
- [支持] 番茄提醒功能，每日上午下午提醒做番茄，提醒番茄后记录，提醒休息后回归番茄，完善番茄工作法

2. 阅读
- [支持] 支持RSS订阅
- [支持] 支持拖动管理订阅排序
- [支持] 支持稍后阅读、支持加星、收藏等
- [支持] 支持分享到社交网络
- [支持] 支持语音播放某篇文章
- [即将支持] 增加针对微博、微信公众号订阅 
- [即将支持] 头条博文、个性推荐博文
- [即将支持] 每日读订阅功能

3. 思维导图
- [支持] 支持快速新增导图 支持增加描述
- [支持] 支持快捷键插入新节点、更改节点等
- [支持] 支持思维导图，导出为图片
- [即将支持] 将喜欢的文章一键生成html文章 
- [即将支持] 更高效的编辑框增加导图描述 

4. 想法
- [支持] 支持标签功能 支持公开或者私密发布
- [支持] 支持chrome等高版本浏览器上面，语音记录想法功能
- [支持] 支持分享网页到想法 自动读取网页标题
- [支持] 支持分享图片到想法
- [支持] 自动引导书写每日小目标 每日总结

5. Kindle订阅推送
- [支持] 支持将订阅推送到你的kindle设备
- [支持] 支持测试推送 支持带图推送
- [即将支持] 自定义推送特定RSS订阅项内容 

6. 统计
- [支持] 按月支持针对阅读、番茄、想法等统计的饼图与柱状图记录
- [即将支持] 更细化的番茄工作法统计，更完善的提醒

7. 日总结
- [支持] 每日提醒
- [支持] 书写日总结时，将会把平台所记录事情、导图、想法、阅读进行罗列辅助

## 高效使用平台

- 快速订阅，chrome浏览器安装 [RSS Subscription Extension](https://chrome.google.com/webstore/detail/rss-subscription-extensio/nlbjncdgjeocebhnmkbbbdekmmmcbfjd) 增加订阅选项之后 点击立即订阅即可
```
录入说明: 订阅到Montage GTD
录入网址：http://task.congcong.us/feeds?url=%s
```

- 快速分享，chrome浏览器安装 [右键搜](https://chrome.google.com/webstore/detail/context-menus/phlfmkfpmphogkomddckmggcfpmfchpn)
```
右键“右键搜标识”选择选项，自定义中进行设置：
页面菜单：https://task.congcong.us/notes?add_content=%s
划词菜单：https://task.congcong.us/notes?add_content=%s
图片菜单：https://task.congcong.us/notes?type=image&add_content=%s
链接菜单：https://task.congcong.us/notes?add_content=%s
```

或者直接在设置界面加载如下配置
```
{"mcGroup":"[]","linBack":"[]","txtSelect":"[\"montage\"]","lb1":"\"2\"","rt2":"\"2\"","shorten":"\"googl\"","txtBack":"[]","zh_TW":"false","menBack":"[]","txtIncognito":"[]","analytics":"true","menSelect":"[\"montage\"]","picIncognito":"[]","picCustom":"[[\"montage\",\"https://task.congcong.us/notes?type=image&add_content=%s\"]]","rb2":"\"2\"","linCustom":"[[\"montage\",\"https://task.congcong.us/notes?add_content=%s\"]]","picBack":"[]","lcGroup":"[]","pcGroup":"[]","qr_size":"250","txtCustom":"[[\"montage\",\"https://task.congcong.us/notes?add_content=%s\"]]","rt1":"\"1\"","isFlag":"true","ru":"false","lt2":"\"1\"","tcGroup":"[]","linSelect":"[\"montage\"]","back":"false","names":"{}","menCustom":"[[\"montage\",\"https://task.congcong.us/notes?add_content=%s\"]]","lt1":"\"1\"","newPage":"true","zh_CN":"false","phrase":"\"montagegtd\"","locale":"\"zh_CN\"","lb2":"\"1\"","picSelect":"[\"montage\"]","rb1":"\"2\"","isEdit":"true","linIncognito":"[]","en":"false","menIncognito":"[]"}
```


## 二次开发或部署

请移步至wiki区域，wiki区域将不断完善技术相关内容

[https://gitee.com/accacc/task/wikis/Home?sort_id=42169](https://gitee.com/accacc/task/wikis/Home?sort_id=42169)

## 鸣谢
![jetbrains](https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg)
感谢[jetbrains](https://jb.gg/OpenSourceSupport)支持