# gnuplot-event-heatmap

Not-so-elaborate-but-works-for-me&trade; PHP snippet that generates a heatmap out of a series of events (a.k.a. timestamps).

  * x = time of day in 15 minute slots
  * y = day of week
  * z = frequency

 
This can be used to find out how events happen or walk-in-visitors distribute over the week.
 
# Usage

Throw `src/index.php` into your webserver root and call it via your browser. Alternatively, use the command line: `php index.php`

# Database schema

```
root@raspberrypi:/var/www/html# echo "select * from log limit 10;" | mysql log
id	timestamp
2	2020-07-29 14:31:31
3	2020-07-29 14:31:46
4	2020-07-29 14:38:17
5	2020-07-29 14:38:55
6	2020-07-29 14:42:28
7	2020-07-30 14:05:54
8	2020-07-30 14:06:11
9	2020-07-30 14:06:18
10	2020-07-30 14:11:07
11	2020-07-30 14:13:29
```

# Example output
refer to subdirectory example-output/
