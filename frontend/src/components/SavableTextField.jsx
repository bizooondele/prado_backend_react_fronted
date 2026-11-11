import React, { useEffect, useState } from 'react';
import { Input, Button, message } from 'antd';

const SaveableTextArea = ({
  initialValue = '',
  onSave,
  buttonText = 'Save',
  placeholder = 'Type something...',
  ...rest
}) => {
  const [value, setValue] = useState(initialValue);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    setValue(initialValue);
  }, [initialValue]);

  const handleSave = async () => {
    try {
      setLoading(true);
      await onSave(value); // Calls your async posting function
      message.success('Successfully saved!');
    } catch (error) {
      console.error('Save failed:', error);
      message.error('Failed to save. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 12, maxWidth: 500 }}>
      <Input.TextArea
        value={value}
        onChange={(e) => setValue(e.target.value)}
        placeholder={placeholder}
        rows={4}
        disabled={loading} // Disable typing while saving
        {...rest}
      />
      <div style={{ display: 'flex', justifyContent: 'flex-end' }}>
        <Button
          type="primary"
          onClick={handleSave}
          loading={loading}
          disabled={!value.trim()} // Optional: Disable button if empty
        >
          {buttonText}
        </Button>
      </div>
    </div>
  );
};

export default SaveableTextArea;