# Abstract
Definitions, assumptions and biznes logic. If in doubt on the implementation details, refer to this file to verify
reasoning behind the decision.

## Architecture
- Domain stores biznes logic and models
- Application stores functionality that uses Domain
  - Ports defne Adapters behaviour via Interfaces and Abstracts
- Adapters can be framework specific but should use Ports from Application

```
+-------------+
|   Domain    |
+-------------+
       ⬆️
+-------------+
| Application |
+-------------+
       ⬆️
+-------------+
|   Adapter   |
+-------------+
```

### RabbitMQ
To make this a more of a robust app I've moved any possibly heavy actions into the RabbitMQ queue,
so we can scale it and handle how we want:
- Fee transition - more like, I didn't know what to do with fee amount, then suspected heavy proces
- Transfer to external banking system

### Chain of Responsibilities pattern
Or more like ChainS... To give us more freedom of implementation and possible insight on the process 
I've chosen to use CoR a pattern to implement validations and actual decision-making when handling 
transfer of founds.
During development, as it normally happens, I had to do add a little diversion to the patter, and now 
we have ChainS or Responsibilities:
```
          Start
       Validation1
       Validation2
           ...
  DecideOnTransactionType
       /          \
   Internal    External -> Queue for later consumption
       \          /
          Finish
```
As you can see we have divergence just before the end (and possibly in the further development even earlier). I, 
personally, don't see that as problem - just wanted to give a heads-up and mark that I'm aware that this is not 
exactly by the GoF book. 

## Assumptions/Biznes logic

#### 1. We can only send credit between two accounts with the same currency
#### 2. Transaction must be between two accounts
Credit cannot be removed without putting it somewhere else. Even withdraws from ATM are transactions between two 
systems, so we have Sender and Receiver (even though receiver is a machine/3rd party service).
#### 3. Transaction fee is 0.5% of a transaction
#### 4. You can only make 3 transactions from your account per day
#### 5. You cannot withdraw credit from not owned account
#### 6. External and internal transaction can have different fee

## Decisions

#### Optimistic lock
I've chosen to use Optimistic Lock and retry strategy to counter race conditions. With optimistic lock we can still use
transactions and don't have to change default behaviour of our Persistence Abstract layer (Doctrine)

## Possible improvements out of scope
#### Archive transactions
In this system we could separate Transaction table into InProgress and Archived.
We would do Update and Insert operations on a smaller table InProgress and search operations on Archive.
We could even implement CQRS for that reason and have some noSQL database serve us this data, seperated per customer in 
documents.
#### Monitoring (Loki, Grafana and more logs)
Currently, there is no insight into what happened and why was there an error (or even where).
