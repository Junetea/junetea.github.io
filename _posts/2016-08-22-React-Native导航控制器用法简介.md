---
layout: post
title:  "React Native导航控制器用法简介"
date:   2016-08-22 20:30:00 +0800
category: React Native, iOS, Android, 跨平台
tags: [React Native, JavaScript, 跨平台]
---

React Native封装了许多控件，并且提供了比较详细的文档和代码片段，但并不是所有的示例都能够清楚的说明这些控件的用法，其中就包括重要的`Navigator`（导航控制器）。官方的文档中贴了许多代码，但并没有说清楚它是怎么工作的。

在iOS中，我们使用`UINavigationController`管理多个页面，每个被它所管理的页面都有一个引用指向所在的导航控制器对象。我们可以很方便的利用这个特性管理页面（`push`和`pop`）。不过React Native中的`Navigator`的使用方式不一样，它使用一个集中的路由机制控制页面的跳转，下面我们会通过一个简单的例子详细进行讲解。

#### 1. 创建React Native项目

React Native可以用于现有的项目，为了简单起见，我们使用一个全新的项目开始。

```shell
$ react-native init NavigatorDemo
This will walk you through creating a new React Native project in ~/NavigatorDemo
Installing react-native package from npm...
Setting up new React Native app in ~/NavigatorDemo
NavigatorDemo@0.0.1 ~/NavigatorDemo
└── react@15.2.1 

To run your app on iOS:
   cd ~/NavigatorDemo
   react-native run-ios
   - or -
   Open ~/NavigatorDemo/ios/NavigatorDemo.xcodeproj in Xcode
   Hit the Run button
To run your app on Android:
   Have an Android emulator running (quickest way to get started), or a device connected
   cd ~/NavigatorDemo
   react-native run-android
```

**react-native**会自动创建一下项目内容：

```shell
$ ls
android	index.android.js index.ios.js ios node_modules package.json
```

其中**index.android.js**和**index.ios.js**分别为Android和iOS应用的入口。为了能够更好地看清楚页面结构，我们新建一个**js**目录用于存放页面代码。

```shell
$ mkdir js
$ cd js
$ touch FirstScene.js
$ touch SecondScene.js
$ ls
FirstScene.js SecondScene.js
```

**FirstScene.js**和**SecondScene.js**为两个页面控件。

#### 2. 准备工作

下面以**index.ios.js**为例进行讲解。默认该文件中有许多自动生成的代码，将其修改为以下样子。

```js
//1. 导入react框架中的内容
import React, { Component } from 'react';
//2. 导入react-native框架中的内容
import {
  //3. 必须，注册应用（App）时使用
  AppRegistry, 
} from 'react-native';

//4. 创建应用
class NavigatorDemo extends Component {
  render() {
    return (
      //5. 编写应用的渲染代码
    );
  }
}

//5. 注册应用组件
AppRegistry.registerComponent('NavigatorDemo', () => NavigatorDemo);
```

可以看出**React Native**应用的结构还是很简单的。接下来就开始编写导航控制器相关内容。在使用之前需要导入一下组件：`Navigator`、`View`、`Text`、`TouchableHighlight`等。

```javascript
import {
  AppRegistry,
  Text,
  View,
  Navigator,
  TouchableHighlight,
} from 'react-native';
```

导入刚才创建的页面组件：

```javascript
import FirstScene from './js/FirstScene.js';
import SecondScene from './js/SecondScene.js';
```

接下来就可以使用这两个JS文件中的内容。

#### 3. Navigator的使用

作为页面管理者，`Navigator`也是一个继承字`Component`的组件，需要最先渲染。

```javascript
class NavigatorDemo extends Component {
  render() {
    return (
      //1. 使用导航控制器
      <Navigator
        //2. 设置初始页面
        initialRoute={{name: 'First'}}
        //3. 设置路由表
        renderScene={(route, navigator) => {
          //4. 更加名称路由
          switch (route.name) {
            case 'First':
              return (
                //4.1 渲染页面，并且传入导航控制器，方便页面内使用
                <FirstScene title={route.name} navigator={navigator}/>
              );
            case 'Second':
              return (
                <SecondScene title={route.name} navigator={navigator}/>
              );
          }
        }}
  	/>
   );
}
```

导航控制器有两个非常重要的属性`initialRoute`和`renderScene`，整个页面的管理就依靠这两个属性的设置。其中`initialRoute`用于设置初始页面（第一个页面）；而`renderScene`设置页面路由信息。

其中`initialRoute`的值就是页面路由时的第一个参数`route`对象，因此可以在里面设置任意需要在路由时使用的信息，其中最重要的是需要有一个能够区分页面的标识，这里我们使用`name`进行区分。

```javascript
//2. 设置初始页面
initialRoute={{name: 'FirstScene'}}
```

`renderScene`是一个路由函数，导航控制器中的每个页面显示前都会调用该函数，从而根据传递的参数（`route`）获取对应的页面。

```javascript
//3. 设置路由表
renderScene={(route, navigator) => {
  //4. 更加名称路由
  switch (route.name) {
    case 'First':
      return (
        //4.1 渲染页面，并且传入导航控制器(navigator)，方便页面内使用
        <FirstScene title={route.name} navigator={navigator}/>
      );
    case 'Second':
      return (
        <SecondScene title={route.name} navigator={navigator}/>
      );
  }
}}
```

#### 4. 页面跳转

官方以及网络上的许多示例都是将所有代码写在`index.ios.js`中，在实际应用中几乎不存在这种情况。因此我们将页面组件分别写在两个不同的文件`FirstScene.js`和`SecondScene.js`中。其中第一个页面内容为：

```javascript
import React, { Component } from 'react';
import {
  View,
  Text,
  TouchableHighlight,
} from 'react-native';

//1. 导出默认类，一定不能忘记export default导出
export default class FirstScene extends Component {

  render() {
    return (
      <View style={{ flex: 1, alignItems: 'center', justifyContent: 'center' }}>
        <TouchableHighlight onPress={() => {
          //2. 使用props获取传入的navigator并使用route对象作为参数，跳转到新的页面
          this.props.navigator.push({name: 'Second'});
        }}>
          <Text>{this.props.title}</Text>
        </TouchableHighlight>
      </View>
    );
  }
}
```

第二个页面的内容为：

```javascript
import React, { Component } from 'react';
import {
  View,
  Text,
  TouchableHighlight,
} from 'react-native';

//1. 导出默认类
export default class SecondScene extends Component {
  render() {
    return (
      <View style={{ flex: 1, alignItems: 'center', justifyContent: 'center' }}>
        <TouchableHighlight onPress={() => {
          //2. 使用传入的导航控制器进行跳转，返回上一页
          this.props.navigator.pop();
        }}>
          //3. 显示页面标题
          <Text>{this.props.title}</Text>
        </TouchableHighlight>
      </View>
    );
  }
}
```

很容易可以看出，其实每个页面也只是一个简单的组件，并不是什么特殊的东西。只不过很多人都被卡在了导航控制器（`navigator`）的获取上，因为**React Native**并不会像iOS一样自动将`navigator`传入页面。

> **注意**：页面跳转时，不管是`push`还是`pop`都会先调用`navigator`的`renderScene`方法。

#### 5. 参考资料

- [https://facebook.github.io/react-native/docs/using-navigators.html](https://facebook.github.io/react-native/docs/using-navigators.html)

- [https://rnplay.org/apps/HPy6UA](https://rnplay.org/apps/HPy6UA)

  ​