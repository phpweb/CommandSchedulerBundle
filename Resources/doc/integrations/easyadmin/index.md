# EasyAdmin integration

If you want to manage your scheduled commands via [EasyAdmin](https://github.com/EasyCorp/EasyAdminBundle) here is a configuration template that you can copy/paste and change to your needs.

## EasyAdmin v.3

This Version uses .php-Files for configuration. 
Copy the file "ScheduledCommandCrudController.php" to the /Controller/ Folder of your EasyAdmin-Installation.


## EasyAdmin v.2

This Version uses .yaml-Files for configuration

```yaml
easy_admin:
  entities:
    Cron:
      translation_domain: 'JMoseCommandScheduler'
      label: 'list.title'
      class: JMose\CommandSchedulerBundle\Entity\ScheduledCommand
      list:
        title: "list.title"
        fields:
          - { property: 'id', label: 'ID' }
          - { property: 'name', label: 'detail.name' }
          - { property: 'command', label: 'detail.command' }
          - { property: 'arguments', label: 'detail.arguments' }
          - { property: 'lastExecution', label: 'detail.lastExecution' }
          - { property: 'lastReturncode', label: 'detail.lastReturnCode' }
          - { property: 'locked', label: 'detail.locked', type: boolean}
          - { property: 'priority', label: 'detail.priority' }
          - { property: 'disabled', label: 'detail.disabled' }
        actions:
          - { name: 'jmose_command_scheduler_action_execute', type: 'route', label: 'action.execute' }
          - { name: 'jmose_command_scheduler_action_unlock', type: 'route', label: 'action.unlock' }
      form:
        fields:
          - { property: 'name', label: 'detail.name' }
          - { property: 'command', label: 'detail.command', type: 'JMose\CommandSchedulerBundle\Form\Type\CommandChoiceType' }
          - { property: 'arguments', label: 'detail.arguments' }
          - { property: 'cronExpression', label: 'detail.cronExpression' }
          - { property: 'priority', label: 'detail.priority' }
          - { property: 'disabled', label: 'detail.disabled' }
          - { property: 'logFile', label: 'detail.logFile' }
      new:
        fields:
          - { property: 'executeImmediately', label: 'detail.executeImmediately' }
```
