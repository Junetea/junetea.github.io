---
layout: post
title:  "打包自己的React Native控件"
date:   2016-09-01 18:38:00 +0800
category: React Native, iOS, Android, 跨平台
tags: [React Native, Component, 跨平台, npm]
---

在开发React Native应用的过程中，我们会封装许多可以重复使用的控件。我们可能会想要将它们通过`npm`分享出去，然后方便在其它项目中用`npm install`重复使用。怎么用`npm`共享这些混合有Objective-C的代码呢？

#### 创建Package

1 . 创建一个新的iOS项目，用`RN`作为类前缀。

![](http://brentvatne.ca/images/packaging/1-new-project.png)

![](http://brentvatne.ca/images/packaging/2-project-name.png)

之所以使用**RN**作为类前缀，是因为**RCT**一般只用在React Native标准库中。

2 . `cd`到项目文件夹中，然后运行`npm init`，并且填写信息。

![](http://brentvatne.ca/images/packaging/3-npm-init.png)

3 . 运行`npm install react-native —save-dev`，并在**package.json**中增加`peerDependencies`信息。

4 . 设置Xcode的头文件搜索路径，使它能够找到React Native的源码。打开Xcode项目，修改**Header Search Paths**。

![](http://brentvatne.ca/images/packaging/4-header-search-paths.png)

![](http://brentvatne.ca/images/packaging/5-header-search-paths.png)

其中高亮选中的一行用于开发环境，而它上面的**$(SRCROOT)/../react-native/React**用在通过`npm install`安装的场景。

5 . 确保所有源代码都被添加到项目并出现在编译列表中。

![](http://brentvatne.ca/images/packaging/6-compile-sources.png)

6 . 用Git管理整个项目，并创建一个良好的**.gitignore**文件（例如：[Github的Objective-C .gitignore](https://github.com/github/gitignore/blob/master/Objective-C.gitignore)）。别忘了在里面添加`node_modules/**/*`。

7 . 提交代码并打上**tag**： `git tag 0.0.1`。

8 . 将代码push到Github上，并用`npm`发布：`git push origin head —tag && npm publish`。如果从来没有用npm发布过项目，这一步会出错。运行`npm adduser`成功后再次用`npm publish`发布。

9 . 现在就可以与你的朋友通过`npm install your-best-package-name`分享这个控件了。

#### 使用控件

1 . `npm install your-package-name --save`

2 . 将静态库添加到项目中进行链接。

![](http://brentvatne.ca/images/packaging/7-add-link.gif)

