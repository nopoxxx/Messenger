//@ts-ignore
import classes from './Input.module.css'

interface InputProps {
	type: 'email' | 'password' | 'submit' | 'checkbox' | 'text'
	title?: string
	value?: string
	checked?: boolean
	onChange?: (event: React.ChangeEvent<HTMLInputElement>) => void
	name?: string
	disabled?: boolean
}

export function Input({
	type,
	title,
	value,
	checked,
	onChange,
	name,
	disabled,
}: InputProps) {
	return (
		<div className={classes.Input}>
			{type !== 'submit' && title && <label htmlFor={name}>{title}</label>}
			<input
				disabled={disabled}
				id={name}
				type={type}
				value={
					type !== 'checkbox' ? (type === 'submit' ? title : value) : undefined
				}
				checked={type === 'checkbox' ? checked : undefined}
				onChange={onChange}
				name={name}
			/>
		</div>
	)
}

export default Input
