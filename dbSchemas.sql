-- Create the table to store Order Details
CREATE TABLE order_details (
    id SERIAL PRIMARY KEY, -- Unique identifier for each order
    amount NUMERIC(10, 2) NOT NULL, -- The payment amount
    gas_name VARCHAR(50) NOT NULL, -- The name/brand of the gas
    invoice VARCHAR(50) NOT NULL, -- Invoice ID
    payment_status BOOLEAN NOT NULL, -- Payment status (true/false)
    phone_number VARCHAR(15) NOT NULL, -- Phone number of the user
    user_email VARCHAR(100) NOT NULL, -- Email of the user
    created_at TIMESTAMP DEFAULT NOW() -- Timestamp of when the order was created
);
