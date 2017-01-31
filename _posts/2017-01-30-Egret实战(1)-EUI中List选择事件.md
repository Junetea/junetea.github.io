---
layout: post
title:  "Egret实战(1)-EUI中List选择事件"
date:   2017-01-30 13:25:00 +0800
category: Egret实战
tags: [Egret, HTML 5, 2D游戏]
---

EUI中的`List`控件与`Scroller`布局结合使用能够按列表或者网格样式显示数据。`List`为每个元素（item）提供了点击事件（`eui.ItemTapEvent`），方便实现选取的功能。

```javascript
class MenuListPanel extends eui.Component {
  	//关联EXML中的List控件
	private list: eui.List;

	public constructor() {
		super();
		this.skinName = "MenuListSkin";
	}

	protected childrenCreated(): void {
		super.childrenCreated();

		var items = [];
		for	(var i: number = 0; i < 100; i++) {
			var data = {img: "egret_icon_png", title: "Item " + i};
			items.push(data);
		}

		this.list.dataProvider = new eui.ArrayCollection(items);
      	//注册事件（eui.ItemTapEvent.ITEM_TAP）
		this.list.addEventListener(eui.ItemTapEvent.ITEM_TAP, this.onItemTap, this);
	}

	private onItemTap(event: eui.ItemTapEvent): void {
		console.log("点击了第" + event.itemIndex + "个");
	}
}
```

> 注意：刚开始使用的时候，发现注册了事件，但是点击没有反应。请确认注册事件的控件是否确实是`eui.List`对象。

![](/image/egret_item_tap_event.png)

根据实际需要，也可以给Item中的子控件添加事件。不过需要根据控件类型选择所支持的事件类型。

> 注意：Egret Wing中使用`console.log`打印结果输出在**调试**面板中。

![](/image/egret_item_tap_debug_output.png)