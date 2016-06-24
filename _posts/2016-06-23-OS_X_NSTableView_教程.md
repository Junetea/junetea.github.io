---
layout: post
title:  "OS X NSTableView教程"
date:   2016-06-23 14:19:00 +0800
category: Mac开发
tags: [Mac开发, Swift, 翻译]
---

> 本文翻译自Ernesto Garcia的《OS X NSTableView Tutorial》，原文地址为：[https://www.raywenderlich.com/118835/os-x-nstableview-tutorial](https://www.raywenderlich.com/118835/os-x-nstableview-tutorial)。

列表视图（NSTableView）是OS X应用中最常用的控件之一，比如Mail（邮件）应用的消息列表，Spotlight的搜索结果等。它使得Mac可以用一个更漂亮的格式显示列表数据。

`NSTableView`按行列显示数据。每行表示给定数据集中的一个模型对象，而每列数据则表示模型对象的特定属性。

![](https://cdn1.raywenderlich.com/wp-content/uploads/2015/12/250x2501.png)

本文将使用`NSTableView`创建一个类似Finder的文件浏览器。在其中我们可以学习到：

- 如何显示一个表视图
- 如何改变它的可视类型
- 如何处理用户交互，比如选择或双击

准备好创建第一个列表视图？继续阅读！

## 开始

[点击下载](http://www.raywenderlich.com/wp-content/uploads/2015/10/FileViewer.baseproject.zip)启动项目，并用Xcode打开。编译运行后可以看到一下结果：

![](https://cdn3.raywenderlich.com/wp-content/uploads/2015/10/build-run-empty.png)

启动项目（Starter）提供了一个空白画布，我们将用来创建文件浏览器。在应用程序打开菜单中选择**File/Open...** （或者使用快捷键**Command+O**）。

![](https://cdn5.raywenderlich.com/wp-content/uploads/2015/10/build-run-fileopen.png)

在弹出窗口中选择一个文件夹并点击**Open**按钮。在终端会打印：

```shell
Represented object: file:///Users/tutorials/FileViewer/FileViewer/
```

这是启动项目里选中的文件夹路径。如果想要了解具体的实现代码，可以查看一下文件：

- **Directory.swift**：包含**Directory**结构体类型表示的目录结构。
- **WindowController.swift**：包含弹出文件夹选择面板的代码，并将选中的路径传递给**ViewController**。
- **ViewController**：包含`ViewController`类的实现，我们将在里面创建一个列表视图并用来显示文件列表。

### 创建列表视图

在项目导航中打开**Main.storyboard**，选择**View Controller Scene**并且拖入一个**table view**。

![](https://cdn3.raywenderlich.com/wp-content/uploads/2015/10/drop-tableView-700x409.png)

下一步，使用自动布局中的**Pin**按钮添加约束：

- **Top**、**Leading**和**Trailing**：0。
- **Bottom: 22**，确保选中了约束菜单中的**View**。

![](http://www.raywenderlich.com/wp-content/uploads/2015/10/tableview-constraints.png)

点击三角形的**Resolve Auto Layout Issues**按钮，选择**Selected Views section**的**Update Frames**。

![](http://www.raywenderlich.com/wp-content/uploads/2015/10/tableview-updateframes.png)

通过更新视图的frame来匹配约束，可以消除自动布局中的警告。下面来看一下刚创建的列表视图的结构：

- 由行列组成
- 每行表示数据集中的一个模型对象
- 每列显示模型的一个特定属性
- 每列可以有一个标题
- 标题描述该列的数据

![](http://www.raywenderlich.com/wp-content/uploads/2015/11/Screen-Shot-2015-11-04-at-7.45.06-AM.png)

如果属性iOS中的`UITableView`，这里也是面临类似的结构，只是OS X要更复杂。事实上，你可能会被`NSTableView`上的独立UI对象的数目吓一跳。

`NSTableView`比`UITableView`更复杂，并且处理的是不同的用户场景。OS X用户一般使用鼠标和触摸板。与`UITableView`的主要区别是`NSTableView`可以有多行多列以及一个可以交互的头部信息，比如排序和选择。它与`NSScorllView`和`NSClipView`一起使用，并且内容可以滚动。控件里包含两个`NSScroller`对象，分别负责表格的水平和垂直滚动。`NSTableView`中包含一定数目的列对象（Column），每一列都有自己的标题。用户可以改变一列的大小和顺序。默认情况下，删除列的功能被禁用了。

### NSTableView剖析

在Interface Builder中，我们可以看到由多个类一起构建的复杂列表结构。

![](https://cdn2.raywenderlich.com/wp-content/uploads/2015/11/Artboard-1-700x377.png)

`NSTableView`的关键部分：

- **Header View**：头部视图是一个`NSTableHeaderView`的对象。它负责绘制将表头绘制在表格顶部。如果你需要显示一个自定义表头，可以创建一个它的子类。
- **Row View**：行视图显示表格中关联每一行的视觉属性，比如高亮。表格的每一行都对应一个行视图的对象，但是行视图并不直接显示内容，而是通过单元格（Cell）显示。行视图只是处理选中颜色、分隔符等视觉属性。我们可以自定义行视图类。
- **Cell Views**：单元格是表格中最重要的对象。在每个行列的交汇处都有一个Cell。每个Cell都是`NSView`或`NSTableCellView`的子类，它们负责实际数据的显示。我们可以通过创建自定义Cell来显示内容。
- **Column**：每一列都代表一个`NSTableViewColumn`对象，负责管理列的宽度和行为，比如大小、排序等。这个类并不是一个视图，而是一个控制器类。我们可以使用它来表示每一行的行为，但是却不能通过它来控制每一行的样式，而应该使用表头视图、行和单元格视图。

> `NSTableView`有两个模式。第一个为基于NSCell 的表格。这种Cell类似一个`NSView`，但是更加老和轻量级。它是早期计算机性能较差的时候出现的。
>
> 苹果推荐使用基于视图的表格视图，但是我们在许多AppKit控件中都能够看到`NSCell`。因此值得我们去了解一下它的由来。更多关于`NSCell`的信息可以查看[Control and Cell Programming Topics](https://developer.apple.com/library/mac/documentation/Cocoa/Conceptual/ControlCell/Concepts/AboutControlsCells.html#//apple_ref/doc/uid/20000731-BBCEACEA)。

在了解了表格视图的基本理论后，我们回到Xcode，并开始使用`NSTableView`。

### 表格视图的列

**Interface Builder**默认为表格视图创建两列，但这里我们需要三列来显示名称、日期和文件尺寸信息。

回到**Main.storyboard**，选择**View Controller Scene**中的表格视图。确保你选择的是**Table View**而不是包含它的**Scroll View**。

![](http://www.raywenderlich.com/wp-content/uploads/2015/10/tableview-select.png)

打开**Attributes Inspector**，将列数(**Columns**)设置为3。很简单，表格视图将包含三列。

下一步，勾选**Selection**中的**Multiple**，因为我们想要一次选则多个文件。同样勾选**Highlight**中的**Alternating Rows**，这样表格就可以像Finder一样交错显示每一行的背景。

![](https://cdn5.raywenderlich.com/wp-content/uploads/2015/10/tableview-attributes.png)

改变每一列的名称，使得它们更具描述性。选择**View Controller Scene**的第一列。

![](http://www.raywenderlich.com/wp-content/uploads/2015/10/tableview-selectcolumn.png)

打开**Attribute Inspector**，将列名从**Title**改为**Name**。

![](http://www.raywenderlich.com/wp-content/uploads/2015/10/tableview-changetitle.png)

对第二列和第三列重复上面的操作，分别将**Title**改为**Modification Date**和**Size**。

> **Note:**另外一个改变列标题的方法是双击表头，使得它可以编辑。两种方法的结果是一样的，任意选择一种就可以。

编译运行程序：

![](https://cdn3.raywenderlich.com/wp-content/uploads/2015/10/tableview-empty2.png)

### 改变信息呈现方式

目前，表格视图包含三列，每一列都包含一个Cell视图。该Cell视图显示了一个文本控件。但这种样式太过单调，因此在文件名附件加上图标表示文件类型。这样表格看起来就更加清晰。我们将第一列的Cell视图替换为包含图片的Cell和一个文本控件。幸运的是Interface Builder内置了这种类型的Cell。

选择**Name**列的**Table Cell View**，并删除。

![](http://www.raywenderlich.com/wp-content/uploads/2015/10/tableview-deletecell.png)

打开**Object Library**，拖入一个**Image & Text Table Cell View**到第一列，并放置在**Name**列。

![](https://cdn1.raywenderlich.com/wp-content/uploads/2015/10/tableview-dropcell.png)

### 设置唯一标识

每个Cell类型都需要设置一个唯一标识，否则无法为特定列创建Cell视图。

选择第一列的Cell视图，并且将**Identity Inspector**中的**Identity**设置为**NameCellID**。

![](http://www.raywenderlich.com/wp-content/uploads/2015/10/tableview-identifier.png)

同样处理第二列和第三列的Cell视图，将它们的标识分别设置为**DataCellID**和**SizeCellID**。

### 显示表格内容

>有两种显示表格内容的方式——使用这个OS X NSTableView教程中讲到的代理或使用Cocoa绑定技术（Cocoa Binding）。当你开始一个项目时就应该在它们之间作出选择。在以后会有Cocoa绑定技术的教程。

现在表格视图还不知道我们需要显示的数据以及如何显示它们，而这些却是必需的。我们通过实现下面两个协议来提供这些信息：

- `NSTableViewDataSource`：提供表格视图所展示的行数。
- `NSTableViewDelegate`：提供每个需要显示的表格视图。

![](https://cdn5.raywenderlich.com/wp-content/uploads/2015/10/population-diagram1.png)

整个显示过程由表格视图、`delegate`和`dataSource`共同完成。

1. 表格视图调用`dataSource`的方法`numberOfRowsInTableView(_:)`。并返回需要显示的行数。
2. 表格视图为每一列的每一行调用`delegate`的方法`tableView(_: viewForTableColumn:, row:)`。`delegate`为该位置创建视图，填充合适的数据，并将它返回给表格视图。

为了在表格中显示数据，需要实现这两个方法。

打开**assisant editor**，按住**Control**并从表格视图拖动到**ViewController**，插入一个`IBOutlet`。

![](https://cdn1.raywenderlich.com/wp-content/uploads/2015/10/tableview-outlet-12.png)

确保连接时的**Type**为`NSTableView`，并且**Connection**类型为**Outle**，**Name**设置为`tableView`。

![](http://www.raywenderlich.com/wp-content/uploads/2015/10/tableview-outlet-22.png)

这样就可以在代码中使用`tableView`来引用这个表格视图了。

在**ViewController.swif**文件最后添加以下代码实现DataSource中的方法。

```swift
extension ViewController : NSTableViewDataSource {
	func numberOfRowsInTableView(tableView: NSTableView) -> Int {
  		return directoryItems?.count ?? 0
	}  
}
```

上面代码创建了一个扩展来实现`NSTableViewDataSource`协议，并完成了必须实现的方法`numberOfRowsInTableView(_:)`来返回文件数目。其中文件数目就是`directoryItems`数组的大小。

下面接着实现`NSTableViewDelegate`协议中的方法。

```swift
extension ViewController : NSTableViewDelegate {
  func tableView(tableView: NSTableView, viewForTableColumn tableColumn: NSTableColumn?, row: Int) -> NSView? {
  		var image: NSImage?
  		var text: String = ""
  		var cellIdentifier: String = ""
  		
  		// 1
  		guard let item = directoryItems?[row] else {
  			return nil
		}
		
		// 2
		if tableColumn == tableView.tableColumns[0] {
  			image = item.icon
  			text = item.name
  			cellIdentifier = "NameCellID"
		} else if tableColumn == tableView.tableColumns[1] {
  			text = item.date.description
  			cellIdentifier = "DateCellID"
		} else if tableColumn == tableView.tableColumns[2] {
  			text = item.isFolder ? "--" : sizeFormatter.stringFromByteCount(item.size)
  			cellIdentifier = "SizeCellID"
		}
		
		// 3
		if let cell = tableView.makeViewWithIdentifier(cellIdentifier, owner: nil) as? NSTableCellView {
  			cell.textField?.stringValue = text
  			cell.imageView?.image = image ?? nil
  			return cell
		}
		return nil
	}
}
```

上面的代码定义了一个扩展实现`NSTableViewDelegate`协议中`tableView(_:viewForTableColumn:row)`方法。该方法被表格视图调用获取Cell对象。这个方法中的内容较多，我们一步一步来看。

1. 如果没有数据显示，则不需要返回Cell对象。
2. 以列为基础设置每个Cell所显示的文字和图片。
3. 使用`makeViewWithIdentifier(_:owner)`获取Cell视图对象。这个方法使用一个标识创建可重用的Cell。然后给Cell对象填充数据。

下一步，在`viewDidLoad()`中添加代码：

```swift
tableView.setDelegate(self)
tableView.setDataSource(self)
```

这两行代码告诉表格视图使用视图控制器作为它的数据源。最后一步是在选取新的目录后更新表格的数据。

第一步，在`ViewController`中添加：

```swift
func reloadFileList() {
  directoryItems = directory?.contentsOrderedBy(sortOrder, ascending: sortAscending)
  tableView.reloadData()
}
```

这是一个辅助更新文件列表的方法。它调用`directory`的`contentsOrderedBy(_:ascending)`方法，返回一个已经排序的文件数组。然后调用`reloadData()`更新表格视图。在选择一个新的目录后，只需要调用这个方法。

在`representedObject`的观察方法`didSet`中将代码：

```swift
print("Represented object: \(url)")
```

替换为：

```swift
directory = Directory(folderURL: url)
reloadFileList()
```

创建一个`Directory`的对象，并且调用`reloadFileList()`方法更新数据。

编译运行程序。使用菜单**File\Open File…**打开文件夹，或者使用快捷键**Command+O**打开，就会看到完整的文件夹内容。

![](http://www.raywenderlich.com/wp-content/uploads/2015/10/tableview-datapopulated.png)

但生活从来不轻松，对吧？仔细看，表格中是不是有一个奇怪的现象，当水平空间不够的时候所有文本都被截取了。

![](http://www.raywenderlich.com/wp-content/uploads/2015/11/Screen-Shot-2015-11-29-at-6.00.19-PM.png)

这是因为Interface Builder创建的Cell没有使用自动布局进行约束。我们可以让Cell自动适应列宽。

### 给Cell添加约束

打开**Main.storyboard**，选择**Name**列Cell中的图片视图。点击**Pin**按钮并添加一下三个约束：

- **Leading**: 3
- **Height**: 17
- **Width**: 17

![](http://www.raywenderlich.com/wp-content/uploads/2015/10/imageview-constraints.png)

保持选中图片视图，点击**Align**按钮，添加**Vertically in Container**的约束。

![](http://www.raywenderlich.com/wp-content/uploads/2015/10/imageview-constraints2.png)

选择同一个Cell中名为**Table View Cell**，类型为`NSTextField`的文本视图。

![](http://www.raywenderlich.com/wp-content/uploads/2015/11/Screen-Shot-2015-11-03-at-11.43.19-PM.png)

添加一下约束：

- **Leading**: 7
- **Trailing**: 3
- **Vertically in Container**对齐约束

![](https://cdn3.raywenderlich.com/wp-content/uploads/2015/10/textfield-name-constraint1.png)![](https://cdn3.raywenderlich.com/wp-content/uploads/2015/10/imageview-constraints21.png)

在**Modification Date**和**Size**列的Cell上重复上面的操作，添加一下约束：

- **Leading**: 3
- **Trailing**: 3
- **Vertically in Container**对齐约束

![](https://cdn2.raywenderlich.com/wp-content/uploads/2015/10/textfield-name-constraint3.png)![](https://cdn3.raywenderlich.com/wp-content/uploads/2015/10/imageview-constraints21.png)

编译运行的结果如下：

![](http://www.raywenderlich.com/wp-content/uploads/2015/10/build-run-autolayout.png)

现在可以看到，当调整列宽到足够空间的时候，文本信息不再被截断了。

### 表视图交互

本节中我们将学习如何与表视图进行交互。

#### 响应用户选择

当用户选择一个或多个文件时，应用程序应该更新底部状态栏显示所选中的文件个数。为了获取表视图的选择动作，我们需要实现Delegate中的`tableViewSelectionDidChange(_:)`方法。在表视图检测到选择发生改变的时候就会调用这个方法。

在`ViewController`中添加一下代码：

```swift
func updateStatus() {
  var text: String = ""
  // 1
  let itemsSelected = tableView.selectedRowIndexes.count
  
  // 2
  if (directoryItems == nil) {
  	text = ""
  }
  else if (itemsSelected == 0) {
  	text = "\(directoryItems!.count) items"
  }
  else {
  	text = "\(itemsSelected) of \(directoryItems!.count) selected"
  }
  
  // 3 
  statusLabel.stringValue = text
}
```

这个方法在用户选择文件后更新标签上的文字信息。

1. 表视图的`selectedRowIndexes`属性包含所有选中列的索引。只要获取该数组的元素个数就可以得到选中了多少个文件。
2. 基于选中的文件个数构建所需要显示的字符串。
3. 设置状态信息。

在代理表视图的代理扩展中添加一下代码：

```swift
func tableViewSelectionDidChange(notification: NSNotification) {
  updateStatus()
}
```

当选择发生改变表视图就会调用该方法。编译运行的结果：

![](http://www.raywenderlich.com/wp-content/uploads/2015/10/build-run-selection.png)

尝试选择更多文件，观察状态信息的改变情况。

### 响应双击

在OS X中，双击一般意味着用户触发一个动作需要执行。在这个例子里，我们双击一个文件后使用默认的应用程序打开它，如果是一个文件夹，则显示里面的内容。下面来实现一下响应双击动作。

双击通知并不是通过表视图的代理发送的，而是作为表视图的目标动作（Target-Action）设置的。为了在视图控制器中接受这些通知，我们需要设置表视图的`target`和`doubleAction`属性。

> **Note:** 目标动作（Target-Action）是Cocoa框架中大部分控件用来通知事件的一种设计模式。如果对它还不熟悉，可以阅读一下苹果官方文档**Cocoa Application Competencies for OS X**中的[Target-Action](https://developer.apple.com/library/mac/documentation/General/Devpedia-CocoaApp-MOSX/TargetAction.html)节。

在`ViewController`的`viewDidLoad()`中添加以下代码：

```swift
tableView.target = self
tableView.doubleAction = "tableViewDoubleClick:"
```

这些代码使视图控制器成为双击动作的对象。一旦用户双击表格就会被调用。

继续添加`tableViewDoubleClick(:)`方法的实现：

```swift
func tableViewDoubleClick(sender: AnyObject) {
  // 1
  guard tableView.selectedRow >= 0, let item = directoryItems?[tableView.selectedRow] else {
  return
  }
  
  if item.isFolder {
  	// 2
  	self.representedObject = item.url
  } else {
  	// 3
  	NSWorkspace.sharedWorkspace().openURL(item.url)
  }
}
```

一步一步解释上面的代码：

- 如果没有选择内容，什么不做。注意，在空白处双击也会调用`tableView.selectedRow`等于-1。
- 如果是文件夹，将`representedObject`属性设置为文件夹的路径，显示文件夹的内容。
- 如果是文件，调用`NSWorkspace`的`openURL()`打开文件。

编译运行程序。双击任意的文件，并且观察它是如何在默认程序中打开的。选择一个文件夹，观察表格视图如何更新和显示它里面的内容。

噢，等一下，这不就已经DIY了一个Finder？看起来像吧！

#### 数据排序

大家都喜欢一个好的顺序，在这一节中，我们将会学习如何基于用户的选择对表视图进行排序。

表视图的一个非常好的特征是在指定列上点一下或两下。点一下升序排列，点两下降序排列。

用`NSTableView`实现这个效果非常简单。我们使用**Sort descriptors**（排序描述符）处理排序。`NSSortDescriptor`类可以描述指定属性的排序方法。

一旦设置了排序描述符，点击一列的头部就会通过代理通知我们具体是按哪一个属性进行排序。这时就可以进行排序了。

设置好排序描述符后，表视图自动提供所有处理排序的UI控件，比如可以点击的头部、箭头，并且通知选择的哪个排序描述符。然而我们需要负责对数据进行排序并更新表视图，从而反映新的顺序。现在知道如何排序了吧？

![](https://cdn3.raywenderlich.com/wp-content/uploads/2015/11/Screen-Shot-2015-11-29-at-6.24.36-PM.png)

在`viewDidLoad()`中添加以下代码创建排序描述符：

```swift
// 1
let descriptorName = NSSortDescriptor(key: Directory.FileOrder.Name.rawValue, ascending: true)
let descriptorDate = NSSortDescriptor(key: Directory.FileOrder.Date.rawValue, ascending: true)
let descriptorSize = NSSortDescriptor(key: Directory.FileOrder.Size.rawValue, ascending: true)

// 2
tableView.tableColumns[0].sortDescriptorPrototype = descriptorName
tableView.tableColumns[1].sortDescriptorPrototype = descriptorDate
tableView.tableColumns[2].sortDescriptorPrototype = descriptorSize
```

上面代码的功能：

- 为每一列创建一个排序描述符，分别指明它们所代表的文件属性顺序。
- 通过设置`sortDescriptorPrototype`将排序描述符添加到对应列。

当用户点击任何一列的头部，表视图都会调用数据源方法`tableView(_: sortDescriptorsDidChange: oldDescriptors:)`，指出现在应该按哪一个描述符进行排序。

在数据源所在的扩展中添加以下代码：

```swift
func tableView(tableView: NSTableView, sortDescriptorsDidChange oldDescriptors: [NSSortDescriptor]) {
  // 1
  guard let sortDescriptor = tableView.sortDescriptors.first else {
  	return
  }
  
  if let order = Directory.FileOrder(rawValue: sortDescriptor.key!) {
  	// 2
  	sortOrder = order
  	sortAscending = sortDescriptor.ascending
  	reloadFileList()
  }
  
  reloadFileList()
}
```

代码解析：

1. 根据用户所点击的列，获取第一个排序描述符。
2. 设置视图控制器的`sortOrder`和`sortAscending`属性并调用`reloadFileList()`。这样就可以得到一个排序好的文件数组，并刷新表视图的内容。

编译运行结果：

![](http://www.raywenderlich.com/wp-content/uploads/2015/10/build-run-sort.png)

点击表视图的任意头部对数据进行排序。再一次点击同一个头部在增序和降序之间进行切换。

恭喜，我们已经创建了一个更好的文件查看器。

## 接下来的事情

完整的项目[下载地址](http://www.raywenderlich.com/wp-content/uploads/2015/12/FileViewerFinal.zip)。

这个OS X NSTableView的教程覆盖了不少内容，我们应该会使用表格视图组织内容了。总的来说，我们涉及了以下内容：

- 表视图的基本结构，包括表头、行、列和单元格（Cell）
- 如何添加更多列
- 如何标识各种组件
- 如何加载数据
- 如何添加约束
- 如何响应用户操作

表视图还有很多可以做的事情，要了解更多，可以阅读以下资源：

- 苹果官方的《[Table View Programming Guide for Mac](https://developer.apple.com/library/mac/documentation/Cocoa/Conceptual/TableView/Introduction/Introduction.html)》
- WWDC 2011 - Session 120视频：《[View Based NSTableView Basic to Advanced](https://developer.apple.com/videos/play/wwdc2011-120/)》
- [TableViewPlayGround](https://developer.apple.com/library/mac/samplecode/TableViewPlayground/Introduction/Intro.html#//apple_ref/doc/uid/DTS40010727)示例包含创建各种自定义表格视图的代码。

如何对本教程有疑问或评论，欢迎加入下面的讨论！