---
layout: post
title:  "基于Kitura的Swift后台开发入门"
date:   2016-06-20 17:06:00 +0800
category: Kitura, Swift, 后台开发
tags: [Kitura, Swift]
---

自从Apple开源Swift并支持Linux系统以来，已经有许多人在为Swift应用在后台开发发力了。今年3月份的时候尝试了一下**PerfectSoft**开发的`Perfect`框架，已经能够初步用于搭建后台服务了。**Perfect**集成了URL路由、MySQL/MongoDB/Redis、Mustache模板引擎、WebSocket等功能，采用模块的方式添加功能，支持Mac和Linux。不过**Perfect**的总体使用感觉还不是很好，模块管理不方便、开发流程比较晦涩。在**WWDC 2016**里，**IBM**的工程师带来了他们在今年5月份开源的**Kitura**框架。不知道苹果是不是想将它作为御用的后台框架，先试用一下。

