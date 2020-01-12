## DPlayer-Typecho
[DPlayer](https://github.com/DIYgod/DPlayer) for typecho

### 使用方式
下载后将文件夹名改为DPlayer上传启用即可

默认不自动播放，弹幕不开启
```
[dplayer url="http://xxx.com/xxx.mp4" pic="http://xxx.com/xxx.jpg"/]
```

开启弹幕
```
[dplayer url="http://xxx.com/xxx.mp4" pic="http://xxx.com/xxx.jpg" danmu="true"/]
```

开启自动播放
```
[dplayer url="http://xxx.com/xxx.mp4" pic="http://xxx.com/xxx.jpg" autoplay="true"/]
```

更多参数待补充

### FAQ

#### 1. Pjax页面切换？

重新加载播放器回调函数
```
loadDPlayer();
```

### LICENSE
MIT © [Volio](https://niconiconi.org)