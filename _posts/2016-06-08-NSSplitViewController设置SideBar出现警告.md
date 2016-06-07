---
layout: post
title:  "NSSplitViewController设置SideBar出现警告"
date:   2016-06-08 07:24:00 +0800
category: Mac开发
tags: [Cocoa控件, 开发笔记]
---

Xcode 7为Mac开发也引入了Storyboard，使得界面开发变得更加简单了。不过对于初学者来说，Cocoa框架本身的复杂度还是没有降低，感觉方便性比起Cocoa Touch差太多了。早上在使用`NSSplitViewController`创建一个分栏应用时，想将左侧设置为侧边栏（SideBar），但是发现一旦设置了该属性，就会出现警告。

```
2016-06-08 07:20:38.367 JTSQLite[4680:180062] WARNING: The SplitView is not layer-backed, but trying to use overlay sidebars.. implicitly layer-backing for now. Please file a radar against this app if you see this.
```

从字面上看，是因为没有将`NSSplitView`设置为使用`CALayer`（与iOS不同，NSView默认并没有底层的CALayer支持）。在**StackOverFlow**上，[rid](http://stackoverflow.com/questions/33724767/splitview-not-layer-backed-but-trying-to-use-overlay-sidebars)给了一个答案：在Storyboard中设置Split View的**Core Animation Layer**。

![](http://i.stack.imgur.com/7WyKz.png)

这样确实会将警告去掉，不过会出现新的问题。在拖动侧边栏的时候，由于重绘（Redraw）导致出现黑色区域。

![](/image/201606080738splitview.png)

[Daniel](http://stackoverflow.com/users/345258/daniel)在评论中表示，只要在`NSWindowController`中设置一下：

```swift
self.contentViewController!.view.wantsLayer = true
```

当然这句话需要在继承`NSWindowController`后写在`viewDidLoad`中。