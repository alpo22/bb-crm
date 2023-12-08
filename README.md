## About

This app is the custom CRM for BinBooker.

### Stack:

- Vite
- React
- PHP

### Running:

Go here: http://binbooker.test/sales-crm/

### Building:

```
npm run build
```

This will build the app and copy to the local web server.

### Deploying:

This app runs locally; it does not deploy anywhere.

### Local development:

```
npm i
npm run dev
```

It's not expected to ever really do this since it is already working.

# TODO:

- Changes in my db will get reflected in EmailOctopus. But if someone unsubscribes via EmailOctopus, how will my db know? Need to run "email-octopus-unsubscribe.php" script once in a while.
