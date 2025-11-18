CREATE DATABASE Rental540;
USE Rental540;
GO
-- 1. LOCATION Table 
CREATE TABLE LOCATION (
    LocationID INT PRIMARY KEY IDENTITY(1,1),
    LocationName VARCHAR(100) NOT NULL,
    StreetAddress VARCHAR(255) NOT NULL,
    City VARCHAR(100) NOT NULL,
    State VARCHAR(50) NOT NULL,
    ZipCode VARCHAR(20) NOT NULL,
    Phone VARCHAR(20),
    ManagerID INT,
    CONSTRAINT UQ_Location_Address UNIQUE (StreetAddress, City, State, ZipCode)
);


-- 2. EMPLOYEE Table (includes mechanics)
CREATE TABLE EMPLOYEE (
    EmployeeID INT PRIMARY KEY IDENTITY(1,1),
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    Position VARCHAR(50) NOT NULL,
    HireDate DATE NOT NULL,
    LocationID INT NOT NULL,
    ManagerID INT,
    Email VARCHAR(100),
    Phone VARCHAR(20),
    IsMechanic BIT DEFAULT 0,
    Specialization VARCHAR(100),
    HourlyRate DECIMAL(8,2),
    Certification VARCHAR(100),
    CONSTRAINT FK_Employee_Location FOREIGN KEY (LocationID) REFERENCES LOCATION(LocationID),
    CONSTRAINT FK_Employee_Manager FOREIGN KEY (ManagerID) REFERENCES EMPLOYEE(EmployeeID)
);


-- 3. VEHICLE_TYPE
CREATE TABLE VEHICLE_TYPE (
    TypeID INT PRIMARY KEY IDENTITY(1,1),
    TypeName VARCHAR(50) NOT NULL UNIQUE,
    DailyRate DECIMAL(10,2) NOT NULL,
    Description VARCHAR(255)
);

ALTER TABLE VEHICLE_TYPE
DROP COLUMN DailyRate;

-- 4. SUPPLIER
CREATE TABLE SUPPLIER (
    SupplierID INT PRIMARY KEY IDENTITY(1,1),
    SupplierName VARCHAR(100) NOT NULL,
    ContactPerson VARCHAR(100),
    Phone VARCHAR(20),
    Email VARCHAR(100)
);

-- 5. CUSTOMER
CREATE TABLE CUSTOMER (
    CustomerID INT PRIMARY KEY IDENTITY(1,1),
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    DateOfBirth DATE NOT NULL,
    DriverLicenseNumber VARCHAR(20) UNIQUE NOT NULL,
    Email VARCHAR(100),
    Phone VARCHAR(20),
    Age AS (DATEDIFF(YEAR, DateOfBirth, GETDATE()))  -- Computed column for age
);

-- 6. CUSTOMER_ADDRESS
CREATE TABLE CUSTOMER_ADDRESS (
    AddressID INT PRIMARY KEY IDENTITY(1,1),
    CustomerID INT NOT NULL,
    StreetAddress VARCHAR(255) NOT NULL,
    City VARCHAR(100) NOT NULL,
    State VARCHAR(50) NOT NULL,
    ZipCode VARCHAR(20) NOT NULL,
    Country VARCHAR(50) DEFAULT 'USA',
    AddressType VARCHAR(20) CHECK (AddressType IN ('Billing','Mailing','Home')) DEFAULT 'Home',
    IsPrimary BIT DEFAULT 0,
    CONSTRAINT FK_CustAddr_Customer FOREIGN KEY (CustomerID) REFERENCES CUSTOMER(CustomerID)
);

-- 7. INSURANCE_OPTION
CREATE TABLE INSURANCE_OPTION (
    OptionID INT PRIMARY KEY IDENTITY(1,1),
    OptionName VARCHAR(50) NOT NULL,
    DailyCost DECIMAL(10,2) NOT NULL,
    Description VARCHAR(255),
    CoverageLimit DECIMAL(12,2)
);

-- 8. CAR 
CREATE TABLE CAR (
    VIN VARCHAR(17) PRIMARY KEY,
    LicensePlate VARCHAR(15) UNIQUE NOT NULL,
    Make VARCHAR(50) NOT NULL,
    Model VARCHAR(50) NOT NULL,
    Year INT NOT NULL,
    Color VARCHAR(30),
    CurrentMileage INT NOT NULL DEFAULT 0,
    LifetimeMileage INT NOT NULL DEFAULT 0,
    Status VARCHAR(20) DEFAULT 'Available' CHECK (Status IN ('Available', 'Currently Rented', 'Under Maintenance')),
    HomeLocationID INT NOT NULL,
    TypeID INT NOT NULL,
    SupplierID INT NOT NULL,
    CONSTRAINT FK_Car_Location FOREIGN KEY (HomeLocationID) REFERENCES LOCATION(LocationID),
    CONSTRAINT FK_Car_Type FOREIGN KEY (TypeID) REFERENCES VEHICLE_TYPE(TypeID),
    CONSTRAINT FK_Car_Supplier FOREIGN KEY (SupplierID) REFERENCES SUPPLIER(SupplierID),
    CONSTRAINT CHK_Car_Year CHECK (Year BETWEEN 2000 AND YEAR(GETDATE()) + 1)
);

-- 9. MAINTENANCE
CREATE TABLE MAINTENANCE (
    MaintenanceID INT PRIMARY KEY IDENTITY(1,1),
    VIN VARCHAR(17) NOT NULL,
    EmployeeID INT,
    ThirdPartyProvider VARCHAR(100),
    MaintenanceDate DATE NOT NULL,
    MaintenanceType VARCHAR(100) NOT NULL,
    Description VARCHAR(MAX),
    Cost DECIMAL(10,2) NOT NULL,
    HoursWorked DECIMAL(4,2),
    NextMaintenanceDate DATE,
    CONSTRAINT FK_Maint_Car FOREIGN KEY (VIN) REFERENCES CAR(VIN),
    CONSTRAINT FK_Maint_Employee FOREIGN KEY (EmployeeID) REFERENCES EMPLOYEE(EmployeeID)
);

-- 10. RESERVATION
CREATE TABLE RESERVATION (
    ReservationID INT PRIMARY KEY IDENTITY(1,1),
    CustomerID INT NOT NULL,
    PickupLocationID INT NOT NULL,
    ReturnLocationID INT,
    TypeID INT NOT NULL,
    StartDate DATE NOT NULL,
    EndDate DATE NOT NULL,
    Status VARCHAR(20) DEFAULT 'Confirmed' CHECK (Status IN ('Confirmed', 'Canceled', 'Completed')),
    CreatedDate DATETIME DEFAULT GETDATE(),
    CONSTRAINT FK_Res_Customer FOREIGN KEY (CustomerID) REFERENCES CUSTOMER(CustomerID),
    CONSTRAINT FK_Res_PickupLoc FOREIGN KEY (PickupLocationID) REFERENCES LOCATION(LocationID),
    CONSTRAINT FK_Res_ReturnLoc FOREIGN KEY (ReturnLocationID) REFERENCES LOCATION(LocationID),
    CONSTRAINT FK_Res_Type FOREIGN KEY (TypeID) REFERENCES VEHICLE_TYPE(TypeID)
);

-- 11. PAYMENT_METHOD
CREATE TABLE PAYMENT_METHOD (
    MethodID INT PRIMARY KEY IDENTITY(1,1),
    MethodName VARCHAR(50) NOT NULL UNIQUE,
    Description VARCHAR(255)
);

-- 12. RENTAL
CREATE TABLE RENTAL (
    RentalID INT PRIMARY KEY IDENTITY(1,1),
    ReservationID INT NULL,
    CustomerID INT NOT NULL,
    VIN VARCHAR(17) NOT NULL,
    PickupLocationID INT NOT NULL,
    ReturnLocationID INT NOT NULL,
    StartDate DATE NOT NULL,
    ScheduledEndDate DATE NOT NULL,
    ActualEndDate DATE,
    StartingMileage INT NOT NULL,
    EndingMileage INT,
    InitialFuelLevel INT CHECK (InitialFuelLevel BETWEEN 0 AND 100),
    ReturnFuelLevel INT CHECK (ReturnFuelLevel BETWEEN 0 AND 100),
    DailyRate DECIMAL(10,2) NOT NULL,
    TotalAmount DECIMAL(12,2),
    Status VARCHAR(20) DEFAULT 'Active' CHECK (Status IN ('Reserved', 'Active', 'Completed', 'Canceled')),
    CONSTRAINT FK_Rental_Reservation FOREIGN KEY (ReservationID) REFERENCES RESERVATION(ReservationID),
    CONSTRAINT FK_Rental_Customer FOREIGN KEY (CustomerID) REFERENCES CUSTOMER(CustomerID),
    CONSTRAINT FK_Rental_Car FOREIGN KEY (VIN) REFERENCES CAR(VIN),
    CONSTRAINT FK_Rental_PickupLoc FOREIGN KEY (PickupLocationID) REFERENCES LOCATION(LocationID),
    CONSTRAINT FK_Rental_ReturnLoc FOREIGN KEY (ReturnLocationID) REFERENCES LOCATION(LocationID)
);

-- 13. RENTAL_CHARGE 
CREATE TABLE RENTAL_CHARGE (
    RentalChargeID INT PRIMARY KEY IDENTITY(1,1),
    RentalID INT NOT NULL,
    ChargeType VARCHAR(50) NOT NULL CHECK (ChargeType IN ('Base', 'LateFee', 'FuelFee', 'DamageFee', 'DifferentLocationFee', 'CleaningFee', 'Insurance', 'Tax')),
    Amount DECIMAL(10,2) NOT NULL,
    Description VARCHAR(255),
    AppliedDate DATETIME DEFAULT GETDATE(),
    CONSTRAINT FK_RChg_Rental FOREIGN KEY (RentalID) REFERENCES RENTAL(RentalID)
);

-- 14. RENTAL_DAMAGE 
CREATE TABLE RENTAL_DAMAGE (
    DamageID INT PRIMARY KEY IDENTITY(1,1),
    RentalID INT NOT NULL,
    Description TEXT NOT NULL,
    Severity VARCHAR(20) CHECK (Severity IN ('Minor', 'Major', 'Severe')),
    RepairCost DECIMAL(10,2) DEFAULT 0,
    AssessedBy INT,
    AssessmentDate DATE,
    PhotoURL VARCHAR(255),
    CONSTRAINT FK_RDamage_Rental FOREIGN KEY (RentalID) REFERENCES RENTAL(RentalID),
    CONSTRAINT FK_RDamage_Assessor FOREIGN KEY (AssessedBy) REFERENCES EMPLOYEE(EmployeeID)
);
ALTER TABLE RENTAL_DAMAGE DROP COLUMN PhotoURL;

-- 15. RENTAL_INSURANCE
CREATE TABLE RENTAL_INSURANCE (
    RentalInsuranceID INT PRIMARY KEY IDENTITY(1,1),
    RentalID INT NOT NULL,
    OptionID INT NOT NULL,
    OptionDailyCost DECIMAL(10,2) NOT NULL,
    CONSTRAINT FK_RIns_Rental FOREIGN KEY (RentalID) REFERENCES RENTAL(RentalID),
    CONSTRAINT FK_RIns_Option FOREIGN KEY (OptionID) REFERENCES INSURANCE_OPTION(OptionID)
);


DROP TABLE PAYMENT;
-- 17. PAYMENT
CREATE TABLE PAYMENT (
    PaymentID INT PRIMARY KEY IDENTITY(1,1),
    RentalID INT NOT NULL,
    MethodID INT NOT NULL,
    Amount DECIMAL(10,2) NOT NULL,
    PaymentDate DATETIME DEFAULT GETDATE(),
    Status VARCHAR(20) DEFAULT 'Completed' 
        CHECK (Status IN ('Pending', 'Completed', 'Failed', 'Refunded')),
    TransactionID VARCHAR(100),
    CardLastFour VARCHAR(4),
    CardBrand VARCHAR(20),
    CONSTRAINT FK_Payment_Rental FOREIGN KEY (RentalID) REFERENCES RENTAL(RentalID),
    CONSTRAINT FK_Payment_Method FOREIGN KEY (MethodID) REFERENCES PAYMENT_METHOD(MethodID)
);

-- Created indexes for performance
CREATE INDEX IX_Customer_Name ON CUSTOMER(LastName, FirstName);
CREATE INDEX IX_Rental_Dates ON RENTAL(StartDate, ScheduledEndDate);
CREATE INDEX IX_Rental_Status ON RENTAL(Status);
CREATE INDEX IX_Car_Status ON CAR(Status);
CREATE INDEX IX_RentalCharge_Rental ON RENTAL_CHARGE(RentalID);
CREATE INDEX IX_CustomerAddress_Customer ON CUSTOMER_ADDRESS(CustomerID);
CREATE INDEX IX_Employee_Location ON EMPLOYEE(LocationID);
GO

ALTER TABLE CUSTOMER
ADD CONSTRAINT CHK_Customer_Age 
CHECK (DateOfBirth <= DATEADD(YEAR, -21, GETDATE())); 

-- 1. Drop foreign key constraints first
ALTER TABLE RENTAL DROP CONSTRAINT FK_Rental_PickupLoc;
ALTER TABLE RENTAL DROP CONSTRAINT FK_Rental_ReturnLoc;

--dropping these columns from the rental table so i can create the pickup and return tables
ALTER TABLE RENTAL
DROP COLUMN 
    PickupLocationID,
    ReturnLocationID,
    ReturnFuelLevel,
    StartingMileage,
    EndingMileage,
    InitialFuelLevel;

ALTER TABLE RENTAL DROP CONSTRAINT CK__RENTAL__ReturnFu__6B24EA82;
ALTER TABLE RENTAL DROP CONSTRAINT CK__RENTAL__InitialF__6A30C649;

CREATE TABLE PickupLocation (
    PickupLocationID INT IDENTITY(1,1) PRIMARY KEY,
    RentalID INT NOT NULL,
    LocationID INT NOT NULL,
    StartingMileage INT NOT NULL,
    InitialFuelLevel INT CHECK (InitialFuelLevel BETWEEN 0 AND 100),
    FOREIGN KEY (LocationID) REFERENCES LOCATION(LocationID),
    FOREIGN KEY (RentalID) REFERENCES RENTAL(RentalID)
);

DROP TABLE IF EXISTS PickupLocation;

CREATE TABLE ReturnLocation (
    ReturnEntryID INT IDENTITY(1,1) PRIMARY KEY,  
    RentalID INT NOT NULL,
    LocationID INT NOT NULL, 
    EndingMileage INT NOT NULL,
    ReturnFuelLevel INT CHECK (ReturnFuelLevel BETWEEN 0 AND 100),
    FOREIGN KEY (LocationID) REFERENCES LOCATION(LocationID),
    FOREIGN KEY (RentalID) REFERENCES RENTAL(RentalID)
);
EXEC sp_rename 'ReturnLocation', 'RETURN_LOCATION';
EXEC sp_rename 'PickupLocation', 'PICKUP_LOCATION';

ALTER TABLE RENTAL
DROP COLUMN TotalAmount;

ALTER TABLE RENTAL
ADD TotalAmount AS (
    CASE 
        WHEN ActualEndDate > ScheduledEndDate THEN
            -- Charge for scheduled days + 25% extra for each late day
            (DATEDIFF(DAY, StartDate, ScheduledEndDate) * DailyRate) +
            (DATEDIFF(DAY, ScheduledEndDate, ActualEndDate) * DailyRate * 1.25)
        ELSE
            -- Charge for scheduled period only (even if returned early)
            DATEDIFF(DAY, StartDate, ScheduledEndDate) * DailyRate
    END
);

