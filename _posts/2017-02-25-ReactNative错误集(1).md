---
layout: post
title:  "React Native错误集(1)-iOS运行出错"
date:   2017-02-25 08:24:00 +0800
category: React Native错误集
tags: [React Native, 跨平台开发, 移动应用]
---

在macOS的命令行中创建和运行React Native应用非常方便，只需要一下两条命令：

```shell
$ react-native init Project01
$ react-native run-ios
```

不过毕竟React Native和Xcode不是同一家的，有时Xcode会发生一点改变导致运行命令出错。其中比较常见的是打包后应用的路径不一致，在运行iOS应用时出错：

```shell
$ react-native run-ios

省略编译信息

** BUILD SUCCEEDED **

Installing build/Build/Products/Debug-iphonesimulator/Project01.app
An error was encountered processing the command (domain=NSPOSIXErrorDomain, code=2):
Failed to install the requested application
An application bundle was not found at the provided path.
Provide a valid path to the desired application bundle.
Print: Entry, ":CFBundleIdentifier", Does Not Exist

Command failed: /usr/libexec/PlistBuddy -c Print:CFBundleIdentifier build/Build/Products/Debug-iphonesimulator/Project01.app/Info.plist
Print: Entry, ":CFBundleIdentifier", Does Not Exist
```

可以看出项目已经编译成功（**BUILD SUCCEEDED**），但是在安装应用程序（app)时出错，找不到**":CFBundleIdentifier"**。

这个错误很奇怪。当我们找到**.app**中的**Info.plist**文件时，里面确实存在**":CFBundleIdentifier"**，也就是我们平时所说的**Bundle ID**。那这到底是什么鬼！其实只要我们在仔细看一眼错误提示：

```shell
Command failed: /usr/libexec/PlistBuddy -c Print:CFBundleIdentifier build/Build/Products/Debug-iphonesimulator/Project01.app/Info.plist
Print: Entry, ":CFBundleIdentifier", Does Not Exist
```

**build/Build/Products/Debug-iphonesimulator/Project01.app/Info.plist**这个路径到底是哪呢？其实是相对于我们的iOS项目的，即**Project01/ios/**。但可惜Xcode默认的目标路径并不在每个项目文件夹中，而是一个同一的目录。

因此解决方法是让项目的构建目录与错误提示中的路径一致。用Xcode打开iOS项目**Project01/ios/Project01.xcodeproj**。

1. 在**File > Project Settings**中进行路径设置。

![](/image/react_native_bug_01.png)

2. 选择**Advanced**。

   ![](/image/react_native_bug_02.png)

3. 选择**Custom**中的**Relative to Workspace**：

![](/image/react_native_bug_03.png)

关闭Xcode项目，重新运行：

```shell
$ react-native run-ios
省略编译信息
** BUILD SUCCEEDED **

The following commands produced analyzer issues:
	Analyze Modules/RCTRedBox.m
(1 command with analyzer issues)

Installing build/Build/Products/Debug-iphonesimulator/Project01.app
Launching org.reactjs.native.example.Project01
org.reactjs.native.example.Project01: 2361
```

这样就一切正常了！同时可以看到**Project01/ios**目录下的**build**目录中有了应用程序的存在。

```shell
$ ls ios/build/Build/Products/Debug-iphonesimulator/
Project01.app			libRCTNetwork.a
Project01.app.dSYM		libRCTSettings.a
Project01Tests.xctest.dSYM	libRCTText.a
include				libRCTVibration.a
libRCTActionSheet.a		libRCTWebSocket.a
libRCTAnimation.a		libReact.a
libRCTGeolocation.a		libcxxreact.a
libRCTImage.a			libjschelpers.a
libRCTLinking.a			libyoga.a
```

