# Events

There are 4 Events actual. This feature is still in development and maybe change over time.

| Event                                | Info                             |
| ------------------------------------ | -------------------------------- |
| `SchedulerCommandCreatedEvent`       | After an command was created     |
| `SchedulerCommandFailedEvent`        | Commands Failed from MonitorCall |
| `SchedulerCommandPreExecutionEvent`  | Before Execution of an command   |
| `SchedulerCommandPostExecutionEvent` | After Execution of an command    |


## EventSubscriber

You can subscribe to Events which are fired from the Bundle.

The file "SchedulerCommandSubscriber.php" is an example how you can subscribe to them.
It will add an additional logging at the moment.
Just copy the file to your /src/EventSubscriber/ Folder and adjust it to your needs.
