--JOIN--
SELECT 
    L.LocationID,
    L.LocationName,
    SUM(P.Amount) AS TotalRevenue
FROM PAYMENT P
JOIN RENTAL R 
    ON P.RentalID = R.RentalID
JOIN PICKUP_LOCATION PL  
    ON R.RentalID = PL.RentalID
JOIN LOCATION L
    ON PL.LocationID = L.LocationID
WHERE P.Status = 'Completed'
GROUP BY L.LocationID, L.LocationName
ORDER BY L.LocationName;


--Subquery--
SELECT 
    c.CustomerID,
    c.FirstName,
    c.LastName,
    SUM(r.TotalAmount) AS TotalSpent
FROM CUSTOMER c
JOIN RENTAL r ON c.CustomerID = r.CustomerID
WHERE r.TotalAmount > (
        SELECT AVG(TotalAmount)
        FROM RENTAL
        WHERE TotalAmount IS NOT NULL
      )
GROUP BY c.CustomerID, c.FirstName, c.LastName
ORDER BY TotalSpent DESC;


--UDF--
IF OBJECT_ID('dbo.fn_GetCustomerAge') IS NOT NULL
DROP FUNCTION dbo.fn_GetCustomerAge;
GO

CREATE FUNCTION dbo.fn_GetCustomerAge (@CustomerID INT)
RETURNS INT
AS
BEGIN
    DECLARE @Age INT;

    SELECT @Age = DATEDIFF(YEAR, DateOfBirth, GETDATE())
    FROM CUSTOMER
    WHERE CustomerID = @CustomerID;

    RETURN @Age;
END;
GO

SELECT 
    CustomerID, 
    FirstName, 
    LastName,
    dbo.fn_GetCustomerAge(CustomerID) AS Age
FROM CUSTOMER;


--PROC--
CREATE OR ALTER PROCEDURE GetCustomerPaymentHistory
    @Email VARCHAR(100)
AS
BEGIN
    SET NOCOUNT ON;

    SELECT 
        c.FirstName + ' ' + c.LastName AS CustomerName,
        p.PaymentDate,
        p.Amount,
        p.Status,
        pm.MethodName,
        r.RentalID
    FROM CUSTOMER c
    JOIN RENTAL r ON c.CustomerID = r.CustomerID
    JOIN PAYMENT p ON r.RentalID = p.RentalID
    JOIN PAYMENT_METHOD pm ON p.MethodID = pm.MethodID
    WHERE c.Email = @Email
    ORDER BY p.PaymentDate DESC;
END;
GO

EXEC GetCustomerPaymentHistory @Email = 'john.smith@gmail.com'


--CTE--
WITH CustomerTotals AS (
    SELECT 
        c.CustomerID,
        c.FirstName,
        c.LastName,
        SUM(p.Amount) AS TotalPaid
    FROM CUSTOMER c
    JOIN RENTAL r ON c.CustomerID = r.CustomerID
    JOIN PAYMENT p ON r.RentalID = p.RentalID
    WHERE p.Status = 'Completed'
    GROUP BY c.CustomerID, c.FirstName, c.LastName
)
SELECT 
    CustomerID,
    FirstName,
    LastName,
    TotalPaid
FROM CustomerTotals
WHERE TotalPaid > (SELECT AVG(TotalPaid) FROM CustomerTotals)
ORDER BY TotalPaid DESC;


--CURSOR--
DECLARE @VIN VARCHAR(17), 
        @Cost DECIMAL(10,2);

DECLARE CarCursor CURSOR FOR
    SELECT VIN, Cost
    FROM MAINTENANCE
    ORDER BY MaintenanceDate DESC;

OPEN CarCursor;

FETCH NEXT FROM CarCursor INTO @VIN, @Cost;

WHILE @@FETCH_STATUS = 0
BEGIN
    PRINT 'Car VIN: ' + @VIN + ' | Last Maintenance Cost: $' + CAST(@Cost AS VARCHAR(20));

    FETCH NEXT FROM CarCursor INTO @VIN, @Cost;
END

CLOSE CarCursor;
DEALLOCATE CarCursor;


--PIVOT--
SELECT *
FROM (
    SELECT 
        VIN,
        MaintenanceType,
        Cost
    FROM MAINTENANCE
) AS SourceTable
PIVOT (
    SUM(Cost)
    FOR MaintenanceType IN ([Oil Change], [Brake Inspection], [Tire Rotation], [Battery Check], [Coolant Flush], [Air Filter Replacement])
) AS PivotTable;


--AFTER TRIGGER-
IF OBJECT_ID('trg_NoSupplierDelete') IS NOT NULL
DROP TRIGGER trg_NoSupplierDelete;
GO

CREATE TRIGGER trg_NoSupplierDelete
ON SUPPLIER
AFTER DELETE
AS
BEGIN
    RAISERROR('Deleting supplier records is not allowed. Archive instead of delete.', 16, 1);
    ROLLBACK TRANSACTION;
END;
GO

INSERT INTO SUPPLIER (SupplierName, ContactPerson, Phone, Email)
VALUES ('Test Supplier', 'John Doe', '555-1234', 'test@supplier.com');

DECLARE @TestSupplierID INT = SCOPE_IDENTITY();
SELECT @TestSupplierID AS InsertedSupplierID;

DELETE FROM SUPPLIER
WHERE SupplierID = @TestSupplierID;