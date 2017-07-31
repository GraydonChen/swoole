# swoole 自搭框架 及 学习
## 学习Swoole需要掌握的基础知识
### 1、进程和线程概念
1) 进程：进程是具有一定独立功能的程序关于某个数据集合上的一次运行活动,进程是系统进行资源分配和调度的一个独立单位.
2) 线程：线程是进程的一个实体,是CPU调度和分派的基本单位,它是比进程更小的能独立运行的基本单位.线程自己基本上不拥有系统资源,只拥有一点在运行中必不可少的资源(如程序计数器,一组寄存器和栈),但是它可与同属一个进程的其他的线程共享进程所拥有的全部资源.
3) 关系：一个线程可以创建和撤销另一个线程;同一个进程中的多个线程之间可以并发执行.相对进程而言，线程是一个更加接近于执行体的概念，它可以与同进程中的其他线程共享数据，但拥有自己的栈空间，拥有独立的执行序列。
4) 区别：进程和线程的主要差别在于它们是不同的操作系统资源管理方式。进程有独立的地址空间，一个进程崩溃后，在保护模式下不会对其它进程产生影响，而线程只是一个进程中的不同执行路径。线程有自己的堆栈和局部变量，但线程之间没有单独的地址空间，一个线程死掉就等于整个进程死掉，所以多进程的程序要比多线程的程序健壮，但在进程切换时，耗费资源较大，效率要差一些。但对于一些要求同时进行并且又要共享某些变量的并发操作，只能用线程，不能用进程。
   * 简而言之,一个程序至少有一个进程,一个进程至少有一个线程.
   * 线程的划分尺度小于进程，使得多线程程序的并发性高。
   * 另外，进程在执行过程中拥有独立的内存单元，而多个线程共享内存，从而极大地提高了程序的运行效率。
   * 线程在执行过程中与进程还是有区别的。每个独立的线程有一个程序运行的入口、顺序执行序列和程序的出口。但是线程不能够独立执行，必须依存在应用程序中，由应用程序提供多个线程执行控制。
   * 从逻辑角度来看，多线程的意义在于一个应用程序中，有多个执行部分可以同时执行。但操作系统并没有将多个线程看做多个独立的应用，来实现进程的调度和管理以及资源分配。这就是进程和线程的重要区别。
### 2、进程间的通信
1) 管道,是Linux支持的最初Unix IPC形式之一，具有以下特点：数据只能单向流动，只能用于父子进程及兄弟进程之间。管道可用于输入输出的重定向，它可以按照一个命令的输出之间是另一个命令的输入。比如，当在某个shell程序键入who│wc -l后，相应shell程序将创建who以及wc两个进程和这两个进程间的管道
2) 信号是软件层次上对中断机制的一种模拟，在原里上，一个进程收到的信号和处理器收到一个中断请求是一样的。信号是异步的，进程不用等待信号的到达再处理其他的操作，信号事件的发生有两个来源：硬件来源(比如我们按下了键盘或者其它硬件故障)；软件来源，最常用发送信号的系统函数是kill,raise,alarm和setitimer以及sigqueue函数，软件来源还包括一些非法运算等操作。
3) 消息队列，消息队列提供了一种从一个进程向另一个进程发送一个数据块的方法。  每个数据块都被认为含有一个类型，接收进程可以独立地接收含有不同类型的数据结构。我们可以通过发送消息来避免命名管道的同步和阻塞问题。但是消息队列与命名管道一样，每个数据块都有一个最大长度的限制。Linux提供了一系列消息队列的函数接口来让我们方便地使用它来实现进程间的通信。它的用法与其他两个System V PIC机制，即信号量和共享内存相似。
4) 信号量
5) 共享内存，速度最快，效率最高的进程间通信方式，进程之间直接访问内存，而不是通过传送数据。但是使用共享内存需要自己提供同步机制。
6) 套接字（unix域协议）socket API原本是为网络通讯设计的，但后来在socket的框架上发展出一种IPC机制，就是UNIX Domain Socket。虽然网络socket也可用于同一台主机的进程间通讯（通过loopback地址127.0.0.1），但是UNIX Domain Socket用于IPC更有效率：不需要经过网络协议栈，不需要打包拆包、计算校验和、维护序号和应答等，只是将应用层数据从一个进程拷贝到另一个进程。UNIX域套接字与TCP套接字相比较，在同一台传输主机的速度前者是后者的两倍。这是因为，IPC机制本质上是可靠的通讯，而网络协议是为不可靠的通讯设计的。UNIX Domain Socket也提供面向流和面向数据包两种API接口，类似于TCP和UDP，但是面向消息的UNIX Domain Socket也是可靠的，消息既不会丢失也不会顺序错乱。值得注意的是，Unix域协议表示协议地址的是路径名，而不是Internet域的IP地址和端口号。
### 3、SOCKET的应用
1) 了解SOCKET的基本操作如accept/connect、send/recv、close、listen、bind
2) 了解SOCKET的接收缓存区、发送缓存区、阻塞/非阻塞、超时等概念
### 4、IO复用
1) 其实IO复用的历史和多进程一样长，Linux很早就提供了select系统调用，可以在一个进程内维持1024个连接。后来又加入了poll系统调用，poll做了一些改进，解决了1024限制的问题，可以维持任意数量的连接。但select/poll还有一个问题就是，它需要循环检测连接是否有事件。这样问题就来了，如果服务器有100万个连接，在某一时间只有一个连接向服务器发送了数据，select/poll需要做循环100万次，其中只有1次是命中的，剩下的99万9999次都是无效的，白白浪费了CPU资源。
直到Linux 2.6内核提供了新的epoll系统调用，可以维持无限数量的连接，而且无需轮询，这才真正解决了C10K问题。现在各种高并发异步IO的服务器程序都是基于epoll实现的，比如Nginx、Node.js、Erlang、Golang。像Node.js这样单进程单线程的程序，都可以维持超过1百万TCP连接，全部归功于epoll技术。
IO复用异步非阻塞程序使用经典的Reactor模型，Reactor顾名思义就是反应堆的意思，它本身不处理任何数据收发。只是可以监视一个socket句柄的事件变化。--引用于Rango博客
### 5、TCP/IP网络协议
1) 明白tcp／ip协议族 通信流程，tcp／ip发包解包的过程，明白各个层里面的协议的用法及原理，通信的方式。
建议书籍《linux高性能服务器编程》、《tcp/ip详解》《图解tcp／ip》相对容易理解
## swoole执行过程
![possess](https://github.com/GraydonChen/swoole/blob/master/image/process.png)
## Swoole的进程/线程模型
![possess](https://github.com/GraydonChen/swoole/blob/master/image/pattern.png)
