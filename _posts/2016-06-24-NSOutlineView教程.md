>感谢**Jean-Pierre Distler**的《[NSOutlineView on OS X Tutorial](https://www.raywenderlich.com/123463/nsoutlineview-os-x-tutorial)》。

编写应用程序时，通常需要展示列表结构的数据，例如展示一个菜单列表。这样的数据使用`NSTableView`很容易搞定。但是如果按开胃菜或者主菜分组怎么办？现在就有一个问题了，因为表视图没有分组功能。你无法搞定你的多级菜单。
![](https://cdn2.raywenderlich.com/wp-content/uploads/2016/02/NSOutlineView-feature.png)
谢天谢地，`NSOutlineView`提供了更多功能。`NSOutlineView`是一个通用的OS X组件。它是`NSTableView`的子类。与表视图类似，它根据行列显示内容；不同的是它使用层级数据结构。
为了近距离看一下`NSOutlineView`，用Xcode打开一个已有的项目。展开项目文件，可以看到下图类似的文件结构。
![](http://www.raywenderlich.com/wp-content/uploads/2015/12/Outline_Xcode.png)

在这个**NSOutlineView on OS X tutorial**教程中，你会学到如何使用`NSOutlineView`显示层级数据。为了做到这一点，你将通过编写一个RSS阅读器应用来从文件加载RSS feeds并显示在`NSOutlineView`中。
##开始
启动项目可以在这里[下载](http://www.raywenderlich.com/wp-content/uploads/2016/02/Reader.zip)到。打开项目看一眼。除了模板本身创建的文件以外再添加一个**Feeds.plist**用来存放RSS信息。当创建数据模型的时候再来仔细查看这个文件。
打开**Main.storyboard**准备创建UI。左边是一个普通的`NSOutlineView`，而右侧的白色区域是一个网页视图（WebView）。这些视图使用一个水平的栈视图（Stack View）进行组合。其中栈视图铺满整个程序。栈视图是最近才出现的用来处理自动布局的好方法。如果还不会使用栈视图，可以看一下Marin写的[`NSStackView`教程](http://www.raywenderlich.com/122295/os-x-stack-views-nsstackview)。
![](https://cdn5.raywenderlich.com/wp-content/uploads/2016/01/Starter_UI.png)

第一个任务：完成UI。双击表头，将标题分别改为**Feed**和**Date**。
![](https://cdn3.raywenderlich.com/wp-content/uploads/2015/12/Change_Header.png)

这样做很简单！选择`NSOutlineView`（**Bordered Scroll View - Outline View\Clip View\Outline View**）。在**Attributes Inspector**将缩进（**Indentation**）改为5，启用浮动分组（**Floats Group Rows**）并禁止重新排序（**Reordering**）。
![](http://www.raywenderlich.com/wp-content/uploads/2016/01/nsoutline-inspector-new.png)

展开**Outline View**并选中**Table Cell View**（**Feed**和**Date**都一样）。
![](http://www.raywenderlich.com/?attachment_id=124505)

改变**Identity Inspector**中**DateCell**的**Identifier**。
![](https://cdn1.raywenderlich.com/wp-content/uploads/2016/01/Change_Cell_Identifier.png)

在**Size Inspector**中将**Width**改为102。在**Feed**上重复这些操作，并将宽度设为320。
展开Cell并选择**Table View Cell**。
![](http://www.raywenderlich.com/?attachment_id=124507)

在**Pin**和**Align**菜单中设置文本控件的约束。
![](http://www.raywenderlich.com/?attachment_id=124508)

重新选择Cell，复制粘贴（**Cmd + C**与**Cmd + V**），然后将**Identifier**改为**FeedItemCell**。现在就有三个不同的Cell了。
![](http://www.raywenderlich.com/?attachment_id=124510)

选择**Date**并在**Identity Inspector**中将**Identifier**改为**DateColumn**；将**Feed**的**Identifier**改为**TitleColumn**。
![](http://www.raywenderlich.com/?attachment_id=124512)

最后一步就是设置`NSOutlineView`的代理（Delegate）和数据源（DataSource）。选择`NSOutlineView`并右击，然后从`dataSource`拖动到代表控制器的蓝色小圆。
![](https://cdn1.raywenderlich.com/wp-content/uploads/2015/12/Add_Delegate.png)

运行程序：
![](https://cdn2.raywenderlich.com/wp-content/uploads/2015/12/First_Run-700x464.png)

显示一个空的界面，并在终端打印信息“非法的数据源”。什么错误？
在给`NSOutlineView`填充数据和消除错误信息之前，你需要一个数据模型。

##数据模型
`NSOutlineView`的数据模型比`NSTableView`所用的的数据模型要复杂一些。正如介绍中提到的，`NSOutlineView`显示一个有层次结构的数据模型，因此数据模型需要能够表示这种结构。每个层次结构需要一个顶层或者根对象。这是一个RSS Feed，Feed的名称就是根。
点击**Cmd + N**创建一个新类。选择**Cocoa Class**并点**Next**。
![](https://www.raywenderlich.com/wp-content/uploads/2015/12/Create_Feed_Class_1-700x496.png)

将类名设置为`Feed`，并使它成为`NSObject`的子类，点击**Next**和**Create**。

![](http://www.raywenderlich.com/wp-content/uploads/2015/12/Create_Feed_Class_2.png)

将自动产生的代码替换为：
```swift
import Cocoa

class Feed: NSObject {
	let name: String
	
	init(name: String) {
		self.name = name
	}
}
```

这样就给类增加了一个`name`属性，并在`init`方法中进行了初始化。你将用一个数组存储一组该类的子节点。在这样做之前，需要先为子节点创建一个类。同上，创建一个名为**FeedItem**的类。打开刚创建的**FeedItem.swift**文件，并将内容替换为：
```swift
import Cocoa

class FeedItem: NSObject {
	let url: String
	let title: String
	let publishingDate: NSDate
	
	init(dictionary: NSDictionary) {
		self.url = dictionary.objectForKey("url") as! String
		self.title = dictionary.objectForKey("title") as! String
		self.publishingDate = dictionary.objectForKey("date") as! NSDate
	}
}
```

`FeedItem`有一个`url`属性，将用来在WebView中加载文章；还有`title`和`publishingDate`属性。初始化器使用字典作为参数。这个对象可以从一个Web服务或者plist文件获取。

回到**Feed.swift**，在`Feed`中添加以下属性：
```swift
var children = [FeedItem]()
```

创建一个数组用于存储`FeedItem`对象。
在`Feed`中增加一个类方法用于加载plist文件：
```swift
class func feedList(fileName: String) -> [Feed] {
	//1
	var feeds = [Feed]()
	
	//2
	if let feedList = NSArray(contentsOfFile: fileName) as? [NSDictionary] {
		//3
		for feedItems in feedList {
			//4
			let feed = Feed(name: feedItems.objectForKey("name") as! String)
			//5
			let items = feedItems.objectForKey("items") as! [NSDictionary]
			//6
			for dict in items {
				//7
				let item = FeedItem(dictionary: dict)
				feed.children.append(item)
			}
			//8
			feeds.append(feed)
		}
	}
	
	//9
	return feeds
}
```

该方法使用文件名作为参数，并返回一个`Feed`对象数组。

1. 创建一个空的`Feed`数组
2. 尝试从文件中加载数组
3. 如果创建成功，则进行遍历
4. 字典中的`name`用于初始化`Feed`对象
5. `items`包含另外一组字典
6. 遍历`items`数组
7. 初始化`FeedItem`，并将新对象添加到`children`数组。
8. 循环结束，`Feed`的每个节点都被添加到了`feeds`数组。
9. 返回`feeds`数组。

打开**ViewController.swift**文件，在**IBOutlet**下添加一个属性用于存放所有`feeds`。

```swift
var feeds = [Feed]()
```

在`viewDidLoad()`中添加以下代码：

```swift
if let filePath = NSBundle.mainBundle().pathForResource("Feeds", ofType: "plist") {
	feeds = Feed.feedList(filePath)
	print(feeds)
}
```

运行项目可以在终端看到以下输出结果：

```shell
[<Reader.Feed: 0x600000045010>, <Reader.Feed: 0x6000000450d0>]
```

这表示成功加载了两个`Feed`对象到`feeds`数组中。

##NSOutlineViewDataSource介绍
至此，你已经告诉`NSOutlineView`使用`ViewController`作为它的数据源。但是`ViewController`还不知道它的新任务，因此需要改变这种情况，从而消除讨厌的错误信息。

在`ViewController`的声明下添加以下扩展。

```swift
extension ViewController: NSOutlineViewDataSource {

}
```

这使得`ViewController`符合`NSOutLineViewDataSource`协议。因为在这个教程中不适用数据绑定的方法，因此你必须实现一些方法来给`NSOutlineView`填充数据。接下来让我们过以下这些方法。

你的`NSOutlineView`需要知道显示多少条目。对此，使用`outlineView(_: numberOfChildrenOfItem:) -> Int`提供数据。

```swift
func outlineView(outlineView: NSOutlineView, numberOfChildrenOfItem item: AnyObject?) -> Int {
	//1
	if let feed = item as? Feed {
		return feed.children.count
	}
	
	//2
	return feeds.count
}
```

这个方法被调用获取`NSOutlineView`每一层的数目。由于你只需要两层，因此实现起来非常直接。

1. 如果`item`是一个`Feed`对象，返回`children`中元素的个数。
2. 否则返回`feeds`中元素的个数。

有一点需要注意：`item`是可选类型（Optional)，用`nil`表示数据模型的根对象。在目前这种情况下，`nil`表示`Feed`；否则`item`将包含该层的父对象。因此`FeedItem`的父对象为`Feed`。

继续！`NSOutlineView`需要知道应该显示给定根对象的哪个字节点。下面的代码与前面很类似：

```swift
func outlineView(outlineView: NSOutlineView, child index: Int, ofItem item: AnyObject?) -> AnyObject {
	if let feed = item as? Feed {
		return feed.children[index]
	}
	
	return feeds[index]
}
```

该方法会检查`item`是否为`Feed`对象，如果是`Feed`对象则返回给定索引(Index)对应的`FeedItem`对象。否则当`item`为`nil`时返回一个`Feed`对象。

`NSOutlineView`一个更强大的特征是可以折叠字节点。首先你需要告诉它哪些可以展开，哪些可以折叠。

```swift
func outlineView(outlineView: NSOutlineView, isItemExpandable item: AnyObject) -> Bool {
	if let feed = item as? Feed {
		return feed.childresn.count > 0
	}
	
	return false
}
```

在这个应用中，只有当`Feed`有字节点时可以被折叠和展开。因此需要检查`item`是否为`Feed`对象，如果是则在子节点数目大于0时返回`true`，否则返回`false`。

运行程序，喔！错误信息没有了，终于显示`NSOutlineView`了！但是等等——你只能看到两个表示能够展开的三角形。如果点击其中一个，会显示更多不可见的行。

![](http://www.raywenderlich.com/?attachment_id=123497)

什么东西出错了？没有——你只是需要再多一个方法。

##NSOutlineViewDelegate介绍
`NSOutlineView`询问代理对象应该如何显示数据，但你还没有实现任何一个代理中的方法。是时候添加`NSOutlineViewDelegate`协议了。
在**ViewController.swift**中再添加一个`ViewController`的扩展。

```swift
extension ViewController: NSOutlineDelegate {

}
```

由于`Feed`和`FeedItem`需要分别显示不同的视图，接下来的方法有一点复杂。让我们一点一点添加。
首先，将方法添加到扩展：

```swift
func outlineView(outlineView: NSOutlineView, viewForTableColumn tableColumn: NSTableColun?, item: AnyObject) -> NSView? {
	var view: NSTableCellView?
	
	//更多代码
	return view
}
```

这时，该代理方法会为每一个`item`返回nil。下一步开始为`Feed`返回一个视图。将下面代码添加到`//更多代码`的注释上面：

```swift
//1
if let feed = item as? Feed {
	//2
	view = outlineView.makeViewWithIdentifier("FeedCell", owner: self) as? NSTableCellView
	if let textField = view?.textField {
		//3
		textField.stringValue = feed.name
		textField.sizeToFit()
	}
}
```

代码解析：

1. 检查`item`是否为`Feed`。
2. 从`NSOutlineView`为`Feed`获取一个视图。通常`NSTableViewCell`包含一个文本控件。
3. 设置文本控件的内容，并调用`sizeToFit()`，使得文本控件或根据内容计算大小。

运行程序就可以看到`Feed`单元格了，但展开的时候还是没有内容。

![](http://www.raywenderlich.com/?attachment_id=123506)

这是因为你只是为`Feed`提供了视图。继续**ViewController.swift**中的`feeds`属性下添加代码：

```swift
let dateFormatter = NSDateFormatter()
```

修改`viewDidLoad()`，并在`super.viewDidLoad()`下添加代码：

```swift
dateFormatter.dateStyle = .ShortStyle
```

添加了一个`NSDateFormatter`对象用于格式化`FeedItem`中的`publishingDate`。
回到`outlineView(_: viewForTableColumn: item:)`并添加一个`else-if`语句到`if let feed = item as? Feed:`。

```swift
else if let feedItem = item as? FeedItem {
	//1
	if tableColumn?.identifier == "DateColumn" {
		//2
		view = outlineView.makeViewWithIdentifier("DateCell", owner: self) as? NSTableCellView
		
		if let textField = view?.textField {
			//3
			textField.stringValue = dateFormatter.stringFromDate(feedItem.publisingDate)
			textField.sizeToFit()
		}
	} else {
		//4
		view = outlineView.makeViewWithIdentifier("FeedItemCell", owner: self) as? NSTableCellView
		if let textField = view?.textField {
			//5
			textField.stringValue = feedItem.title
			textField.sizeToFit()
		}
	}
}
```

代码解析：

1. 如果`item`是一个`FeedItem`对象，填充`title`和`publishingDate`两列。使用`identifier`来区分每一列。
2. 如果`identifier`为`dateColumn`，获取一个`DateCell`对象。
3. 使用日期格式化器创建一个`publishingDate`字符串。
4. 如果不是`dateColumn`，为`FeedItem`创建一个单元格。
5. 设置`FeedItem`的标题。

运行程序：

![](http://www.raywenderlich.com/?attachment_id=123507)

还有一个问题——`Feed`的日期列显示一个静态文本。将`if let feed = item as? Feed`下的语句改为：

```swift
if tableColumn?.identifier == "DateColumn" {
	view = outlineView.makeViewWithIdentifier("DateCell", owner: self) as? NSTableCellView
	if let textField = view?.textField {
		textField.stringValue = ""
		textField.sizeToFit()
	}
} else {
	view = outlineView.makeViewWithIdentifier("FeedCell", owner: self) as? NSTableCellView
	if let textField = view?.textField {
		textField.stringValue = feed.name
		textField.sizeToFit()
	}
}
```

在用户选择一个网页时，需要通过WebView进行显示。怎么做到这一点？幸运的是下面的代理方法可以检测哪个网页被选中了。

```swift
func outlineViewSelectionDidChange(notification: NSNotification) {
	//1
	guard let selectedIndex = notification.object?.selectedRow else {
		return
	}
	
	//2
	if let feedItem = notification.object?.itemAtRow(selectedIndex) as? FeedItem {
		//3
		let url = NSURL(string: feedItem.url)
		//4
		if let url = url {
			//5
			self.webView.mainFrame.loadRequest(NSURLRequest(URL: url))
		}
	}
}
```

代码解析：

1. 检查是否有被选中的行，如果没有就直接返回
2. 检查被选中的行是一个`FeedItem`还是`Feed`
3. 如果一个`FeedItem`被选中了，创建一个`NSURL`对象。
4. 检查是否创建了一个`url`
5. 加载页面

在测试之前，打开**Info.plist**。添加一个新的条目**App Transport Security Settings**并且设置为`Dictionary`类型。然后给它添加一个**Allow Arbitrary Loads**条目，并设置为`YES`。

>**Note:** 这个条目让应用程序可以使用HTTP访问网站，可能会有安全问题。通常来说，只添加**Exception Domians**里的例外域名甚至直接使用HTTPS更安全。

![](http://www.raywenderlich.com/wp-content/uploads/2015/12/Change_Info_Plist.png)

编译运行并选择一个`FeedItem`，假设你可以上网，将在右侧显示一个网页。

###完成触摸
你的应用程序现在可以工作了，但是至少还有两个行为还没有：展开和收起一个组以及删除一个条目。
让我们从双击开始。点击**Alt + Cmd + Enter**打开**Assistant Editor**，点击**Main.storyboard**使得左侧编辑器显示界面，右侧编辑器显示**ViewController.swift**。
右击`NSOutlineView`，在弹出菜单中找到**doubleAction**。

![](http://www.raywenderlich.com/?attachment_id=124518)

从小圆圈拖动到**ViewController.swift**并添加一个`IBAction`，命名为`doubleClickedItem`。确保`Type`为`NSOutlineView`而不是`AnyObject`。

![](https://cdn4.raywenderlich.com/wp-content/uploads/2016/01/AddAction-480x230.png)

切换回标准编辑器（**Cmd + Enter**）并打开**ViewController.swift**。在动作方法中添加以下代码：

```swift
@IBAction func doubleClickedItem(sender: NSOutlineView) {
	//1
	let item = sender.itemAtRow(sender.clickedRow)
	
	//2
	if item is Feed {
		//3
		if sender.isItemExpanded(item) {
			sender.collapseItem(item)
		} else {
			sender.expandItem(item)
		}
	}
}
```

代码解析：

1. 获取被点击的对象
2. 检查是否为一个`Feed`对象，只有`Feed`可以折叠和展开
3. 如果是一个`Feed`对象，根据当前的状态展开或折叠该对象

编译运行程序，然后在一个feed上进行双击。
最后一个行为是让用户通过点击**backspace**或**delete**键删除选中的文章或feed。
仍然是在**ViewController.swift**中添加下面方法到`ViewController`中。直接加载类中，而不是在一个扩展中，因为这个方法与代理或数据源无关。

```swift
override func keyDown(theEvent: NSEvent) {
	interpretKeyEvents([theEvent])
}
```

在用户按键盘时，该方法被自动调用并且告诉系统进行处理。对于一些特殊的键，系统会调用一些功能。如果是*delete*键，则调用`deleteBackward(_:)`。
在`keyDown(_:)`方法中添加下面代码：

```swift
override func deleteBackward(sender: AnyObject?) {
	//1
	let selectedRow = outlineView.selectedRow
	if selectedRow == -1 {
		return
	}
	
	//2
	outlineView.beginUpdates()

	outlineView.endUpdates()
}
```

1. 如果没有内容被选中，`selectedRow`返回-1。
2. 如果有内容被选中，通知`NSOutlineView`更新UI。

在`beginUpdates()`和`endUpdates()`之间添加代码：

```swift
//3
if let item = outlineView.itemAtRow(selectedRow) {
	//4
	if let item = item as? Feed {
		//5
		if let index = self.feeds.indexOf({$0.name == item.name}) {
			//6
			self.feeds.removeAtIndex(index)
			//7
			outlineView.removeItemsAtIndexes(NSIndexSet(index: selectedRow), inParent: nil, withAnimation: .SlideLeft)
		}
	}
}
```

代码解析：

3. 获取被选中的条目
4. 检查是否为`Feed`或`FeedItem`
5. 如果是`Feed`，搜索它在`feeds`数组中的位置
6. 如果找到了，从数组中移除
7. 使用一个小动画将对应行从`NSOutlineView`中移除

继续增加下面代码完成该方法：

```swift
else if let item = item as? FeedItem {
	//8
	for feed in self.feeds {
		//9
		if let index = feed.children.indexOf({$0.title == item.title}) {
			feed.children.removeAtIndex(index)
			outlineView.removeItemsAtIndexes(NSIndexSet(index: index), inParent: feed, withAnimation: .SlideLeft)
		}
	}
}
```

9. 与`Feed`的代码类似，只是还要迭代所有`Feed`下的`FeedItem`，因为不知道选中的`FeedItem`属于哪个`Feed`
10. 对于每个`Feed`都需要查找，是否包含选中的`FeedItem`，如果找到了就删除。

>**Note: **你不仅能够删除一行，而且可以添加或移动这些行。并且它们的步骤都差不多：添加数据到数据模型，然后调用`insertItemsAtIndexes(_:, inParent:, withAnimation:)`插入条目或者调用`removeItemAtIndex(_: inParent:, toIndex:, inParent:)`删除条目。确保数据源发生对应的改变。

现在就完成了你的应用了！编译运行并测试新增的功能。选择一个条目并点击**delete**键，该条目立即消失了！

##更多信息？

恭喜你创建了一个RSS阅读器，并且允许用户可以删除数据或者通过双击展开和收起列表。可以到这里[下载最终代码](http://www.raywenderlich.com/wp-content/uploads/2016/02/Reader_Final.zip)。

在这个**NSOutlineView on OS X tutorial**你学到了很多关于`NSOutlineView`的知识。

- 如何在Interface Builder中添加`NSOutlineView`
- 如何使用`NSOutlineView`显示数据
- 如何展开和折叠数据
- 如何移除条目
- 如何响应用户交互

还有许多内容你还没有机会涉及到，比如拖动数据。如果想要了解更多关于`NSOutlineView`，可以查看[文档](https://developer.apple.com/library/mac/documentation/Cocoa/Reference/ApplicationKit/Classes/NSOutlineView_Class/)。由于`NSOutlineView`是`NSTableView`的子类，因此**Ernesto Garcia**的[表视图教程](http://www.raywenderlich.com/118835/os-x-nstableview-tutorial)也是值得一看的。

期待你喜欢这个教程！如果有任何的问题和评价都可以在下面进行讨论。