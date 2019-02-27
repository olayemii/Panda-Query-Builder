
## **Panda Query Builder**
A PHP7 MYSQL query builder 

## **Setting Up**

 - Rename the `.env.example` file in the root directory to `.env` and change all details in the file.
 - To begin using the query builder, you need to create a Query Builder
   instance from the QB factory

    $userTable = QB::table("users");

We can then access all other method from the `$userTable` variable

## **Selecting Records**

To select a record, invoke the select method on the instance and the get method to actually execute the query, this returns an associative array with the records.

`$userTable->select()->get()`

If no columns are specified, the default column is the wildcard * which selects all columns in the table, to specify columns, pass them as arguments to the select method

`$userTable->select("name", "age", "height")->get()`

**Distinct**
To select distinct records, chain the distinct method to a select method
`$userTable->select("name", "age", "height")->distinct()->get()`

**Count**
To get count of records rows, chain the count method to a select method
`$userTable->select("name", "age", "height")->count()`

**OrderBy**

`$userTable->select("name", "age", "height")->orderBy("id", "ASC")`
Select records and orders the id in an order, arguments can either be `ASC or DESC`

**GroupBy**

`$userTable->select("name", "age", "height")->groupBy("country")`
Select records and groups them based on the column passed as parameter.


## **Adding conditions**

**Adding WHERE conditions**

`$userTable->select()->where("id", "=", "20")->get();`

This selects the user having the id 20 from the users table.

**Adding OR WHERE conditions**

    
`$userTable->select()->where("id", ">", "20")->orWhere("name","OLayemii")->get();`

This selects all records having an id greater than 20 or a name equals to OLayemii

 **WHERE IN**

`$userTable->select()->whereIn("id", [1,2,3])->get();`

Selects records having either id of 1, 2 or 3

**WHERE NOT IN**

`$userTable->select()->whereNotIn("id", [1,2,3])->get();`
Selects records with id that are not 1,2 or 3

**WHERE BETWEEN**
You can also select rows that fall between a range

`$userTable->select()->whereBetween("id", [1,100])->get();`
Selects records with id that are not 1,2,3 . . .100

**WHERE NOT BETWEEN**

`$userTable->select()->whereNotBetween("id", [1,100])->get();`
Selects records with id that are not between 1,2,3 . . .100
 
**IS NULL**

`$userTable->select()->isNull("id")->get();`
Selects records with an id of NULL

**IS NOT NULL**

`$userTable->select()->isNull("id")->get();`
Selects records with an id that is NOT NULL 

**MULTIPLE WHERE CONDITIONS**
To check for multiple conditions, pass an array of arrays with conditions to the where/orWhere method

`$userTable->select()where([
["name","OLayemii"],
["age", ">", "30"]
])->get();`

This query selects a users with name of OLayemii and an id greater than 30.
Notice the default operator is **=** so `["id", "5"]` is also same as `["id", "=", "5"]`


## **Inserting Records**

Panda Query Builder provides two methods to insert records `insert()` and `insertGetId()`

**Insert()**

To insert, pass the insert values as a mapping of column (in the db table) to value (to be inserted).

`$userTable->insert(["username" => "OLayemii", "age" => 10, "eye_color" => "Brown"])`

This method returns a boolean (true / false) unlike the `insertGetId()` which returns the last inserted id of an auto-incrementing column in the table.


## **Updating Records**
To update, pass the update values as a mapping of column (in the db table) to value (to be inserted).

**update()**

`$userTable->update(["username" => "OLayemii"])`

The above query will change the username field of all records in the specified table to "OLayemii" , to be specific on which record to update, chain a where method with the conditions.

`$userTable->update(["username" => "OLayemii"])->where("id", ">", 30)`

**increment()**

The increment method is used to update all records in the table.
It can only be used to increment numerical data on the table.

To increment all values on column on the user table named `reputations` for all user

`$userTable->increment("reputations")`

The increment method accepts an optional second parameter for specifying the increment value

`$userTable->increment("reputations", 200)`

To increment all reputations by 200

**decrement()**

The decrement() method works like the increment() but reduces the values instead

## **Deleting Records**

**delete()**

The delete method performs the delete operation on a table, specific records are first specified by a where method, if a where method is omitted, a truncate operation is carried out (empties all records in the table)

`$userTable->where("name", "Fred")->delete()`

Deletes all records having a name of "Fred"

## **Joins**
Panda Query Builder comes with methods to perform

 - Left Joins
 - Right Joins
 - Inner Joins


**leftJoin()**

To perform left joins, use the leftjoin() method.

`$userTable->select("users.name", "countries.name", "user.age")->leftJoin("countries", "users.id", "=", "countries.id")->get()`

This produces 

SELECT `users.name`, `countries.name`, `user.age` FROM `users`LEFT JOIN countries ON `users.id` = countries.id

**rightJoin()**

To perform left joins, use the rightJoin() method.

`$userTable->select("users.name", "countries.name", "user.age")->rightJoin("countries", "users.id", "=", "countries.id")->get()`

This produces 

	SELECT `users.name`, `countries.name`, `user.age` FROM `users`RIGHT JOIN countries ON `users.id` = countries.id

**join()**

To perform left joins, use the join() method.

`$userTable->select("users.name", "countries.name", "user.age")->join("countries", "users.id", "=", "countries.id")->get()`

This produces 

	SELECT `users.name`, `countries.name`, `user.age` FROM `users` JOIN countries ON `users.id` = countries.id


## **Registering Events**

Panda Query Builder comes with 8 events which can be registered

 1. before-select
 2. after-select
 3. before-insert
 4. after-insert
 5. before-delete
 6. after-delete
 7. before-update
 8. after-update
You can register the events and set an operation to be performed (e.g mailing a user on registration, getting last insert id and using it to insert to a different table) before and after CRUD operations

> Register events before making DB operations

    QB::registerEvent(eventType, $Table, callBackFunction )

**Registering an event before selecting a record**

    QB::registerEvent("before-select", "users", function(){
    		echo "This will happen each time before you perform a select operation on the users table";
    });

**Getting Last Insert Id**
To get the last insert id (i.e after an insert operation)

    QB::registerEvent("after-insert", "users", function($userId){
	    QB::table("countries")->insert(["user_id" => $userId, "country" => "Nigeria"]);
    });


> Any variable passed into the callback function will be used as the
> variable during other operations in the callback
