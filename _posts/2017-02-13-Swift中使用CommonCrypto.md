---
layout: post
title:  "Swift中使用CommonCrypto"
date:   2017-02-13 21:35:00 +0800
category: Swift翻译
tags: [Swift, iOS开发]
---

> 本文翻译自《[Using CommonCrypto in Swift](http://iosdeveloperzone.com/2014/10/03/using-commoncrypto-in-swift/)》，作者：**idz**。

由于CommonCrypto并不是一个独立的模块，因此不能直接使用`import CommonCrypto`引入，而需要一些小技巧才能在Swift中使用。在本文中我将介绍三个方法，大家可以根据具体的场景进行选择。

#### 桥接头文件（Bridging Header）

如果只是想简单地在应用中使用`CommonCrypto`里的方法，最容易的解决方案是在项目中增加混合使用Objective-C的桥接头文件，并在其中增加`#import <CommonCrypto/CommonCrypto.h>`。

通过在项目中创建一个新的Objective-C类，可以让Xcode帮助我们自动生成一个桥接头文件。一旦有了桥接头文件，就可以删除之前所增加的空类。

我们也可以手动创建桥接头文件，但是要稍微麻烦一点。首先需要创建一个头文件，并且打开**Build Settings**，然后找到**Swift Compiler Code Generation**，然后设置**Objective-C Bridging Header**为头文件的路径。

使用桥接头文件的好处是不需要再在Swift代码中使用`import CommonCrypto`语句。

#### 本地*Module Map*

桥接头文件的方式虽然在开发应用的时候可以工作，但是如果我们尝试构建一个**framework**来封装`CommonCrypto`。那么这种方式就会失效。Xcode不允许在框架中使用桥接头文件。

这时就需要使用`Module Map`文件。首先找一个合适的地方创建一个`CommonCrypto`目录。在这个目录下新建一个`module.map`文件并且将下面的内容复制到文件中。我们可能需要根据实际情况修改路径。

```sh
module CommonCrypto [system] {
  header "/Applications/Xcode.app/Contents/Developer/Platforms/iPhoneOS.platform/Developer/SDKs/iPhoneOS.sdk/usr/include/CommonCrypto/CommonCrypto.h"
  header "/Applications/Xcode.app/Contents/Developer/Platforms/iPhoneOS.platform/Developer/SDKs/iPhoneOS.sdk/usr/include/CommonCrypto/CommonRandom.h"
  export *
}
```

为了让这个模块对Xcode可见，打开**Build Setting**->**Swift Compiler**->**Search Paths**，在**Import Paths**中增加`CommonCrypto`文件夹的路径。

现在就可以在Swift代码中使用`import CommonCrypto`了。每个需要使用该框架的项目都应该设置他们的**Import Paths**，这样Xcode才能发现该模块。

#### 全局假模块

Swift Playground对试验和学习API的使用很有帮助。不过上面的两种方法都不能够在Playground中使用。因此只好在SDK目录下创建一个假的`CommonCrypto.framework`，这样Playground就能够找到这些文件了。

首先，打开终端，并切换到模拟器的`Frameworks`目录。下面是最简便的方法：

```sh
cd `xcrun -sdk iphonesimulator -show-sdk-path`/System/Library/Frameworks
```

创建一个`CommonCrypto.framework`目录，并且跟上面一样创建一个`module.map`文件。这时就可以在Playgournd中使用`import CommonCrypto`。

这个方法可以让`CommonCrypot`在所有的应用、框架和Playground中可用。不过有开发者指出这种方式会**污染**SDK，但在官方解决这个问题之前这是唯一的办法。

#### 调用CommonCrypto

不管使用哪种方法引用CommonCrypto，我们都需要知道如何在Swift中调用API。下面是一个使用它来计算MD5的代码：

```swift
var s = "The quick brown fox jumps over the lazy dog."
var context = UnsafeMutablePointer<CC_MD5_CTX>.alloc(1)
var digest = Array<UInt8>(count:Int(CC_MD5_DIGEST_LENGTH), repeatedValue:0)
CC_MD5_Init(context)
CC_MD5_Update(context, s, 
        CC_LONG(s.lengthOfBytesUsingEncoding(NSUTF8StringEncoding)))
CC_MD5_Final(&digest, context)
context.dealloc(1)
var hexString = ""
for byte in digest {
    hexString += String(format:"%02x", byte)
}
println(hexString)
```

我正在开发一个框架让CommonCrypto的使用变得更加简单，但现在还没有完全开发好（注：已经开发完成）。大家可以在Github上找到这个项目：[IDZSwiftCommonCrypto](http://bit.ly/SwiftCrypto)。

#### 总结

这些方法只在Xcode 6.0.1（6A317）上测试过，换一个Xcode版本可能会失效。（注：Xcode 8上有效）如果想要在框架中使用`CommonCrypto`，应该使用方法2和方法3来确保正确连接。