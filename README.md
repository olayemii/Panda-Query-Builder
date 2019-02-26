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

