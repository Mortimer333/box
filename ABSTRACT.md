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

## Assumptions/Biznes logic

#### 1. We can only send credit between two accounts with the same currency
#### 2. Transaction must be between two accounts
Credit cannot be removed without putting it somewhere else. Even withdraws from ATM are transactions between two 
systems, so we have Sender and Receiver (even though receiver is a machine/3rd party service).
#### 3. Transaction fee is 0.5% of a transaction
#### 4. You can only make 3 transactions from your account per day

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
